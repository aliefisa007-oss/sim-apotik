<?php

namespace Tests\Feature\Purchasing;

use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PenerimaanBarangService;
use App\Services\PurchaseOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenerimaanBarangServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_penerimaan_tanpa_po_menambah_stok_dan_membuat_batch(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();

        $penerimaan = app(PenerimaanBarangService::class)->receive(
            data: [
                'po_id' => null,
                'supplier_id' => $supplier->id,
                'tanggal_terima' => now()->toDateString(),
                'no_faktur_supplier' => 'FKT-001',
            ],
            items: [[
                'obat_id' => $obat->id,
                'no_batch' => 'BATCH-X',
                'tanggal_produksi' => null,
                'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
                'harga_beli' => 8000,
                'jumlah' => 100,
            ]],
            userId: $user->id,
        );

        $this->assertCount(1, $penerimaan->detail);
        $this->assertNotNull($penerimaan->detail->first()->batch_id);
        $this->assertDatabaseHas('batch_obat', ['obat_id' => $obat->id, 'no_batch' => 'BATCH-X', 'stok_saat_ini' => 100]);
        $this->assertDatabaseHas('kartu_stok', [
            'jenis_transaksi' => 'masuk_pembelian',
            'jumlah' => 100,
            'referensi_type' => \App\Models\PenerimaanBarang::class,
            'referensi_id' => $penerimaan->id,
        ]);
    }

    public function test_penerimaan_penuh_via_po_mengubah_status_selesai(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();

        $po = app(PurchaseOrderService::class)->create(
            data: ['supplier_id' => $supplier->id, 'tanggal_po' => now()->toDateString()],
            items: [['obat_id' => $obat->id, 'jumlah_order' => 50, 'harga_satuan' => 9000]],
            userId: $user->id,
        );
        app(PurchaseOrderService::class)->kirim($po);

        app(PenerimaanBarangService::class)->receive(
            data: ['po_id' => $po->id, 'supplier_id' => $supplier->id, 'tanggal_terima' => now()->toDateString(), 'no_faktur_supplier' => null],
            items: [[
                'obat_id' => $obat->id,
                'no_batch' => 'BATCH-Y',
                'tanggal_produksi' => null,
                'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
                'harga_beli' => 9000,
                'jumlah' => 50,
            ]],
            userId: $user->id,
        );

        $this->assertSame(PurchaseOrder::STATUS_SELESAI, $po->fresh()->status);
        $this->assertSame(50, $po->fresh()->detail->first()->jumlah_diterima);
    }

    public function test_penerimaan_sebagian_via_po_mengubah_status_diterima_sebagian(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();

        $po = app(PurchaseOrderService::class)->create(
            data: ['supplier_id' => $supplier->id, 'tanggal_po' => now()->toDateString()],
            items: [['obat_id' => $obat->id, 'jumlah_order' => 100, 'harga_satuan' => 9000]],
            userId: $user->id,
        );
        app(PurchaseOrderService::class)->kirim($po);

        app(PenerimaanBarangService::class)->receive(
            data: ['po_id' => $po->id, 'supplier_id' => $supplier->id, 'tanggal_terima' => now()->toDateString(), 'no_faktur_supplier' => null],
            items: [[
                'obat_id' => $obat->id,
                'no_batch' => 'BATCH-Z',
                'tanggal_produksi' => null,
                'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
                'harga_beli' => 9000,
                'jumlah' => 40,
            ]],
            userId: $user->id,
        );

        $this->assertSame(PurchaseOrder::STATUS_DITERIMA_SEBAGIAN, $po->fresh()->status);
    }

    public function test_po_selesai_tidak_bisa_menerima_barang_lagi(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();

        $po = app(PurchaseOrderService::class)->create(
            data: ['supplier_id' => $supplier->id, 'tanggal_po' => now()->toDateString()],
            items: [['obat_id' => $obat->id, 'jumlah_order' => 10, 'harga_satuan' => 9000]],
            userId: $user->id,
        );

        $itemPayload = [[
            'obat_id' => $obat->id,
            'no_batch' => 'BATCH-FULL',
            'tanggal_produksi' => null,
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'harga_beli' => 9000,
            'jumlah' => 10,
        ]];

        app(PenerimaanBarangService::class)->receive(
            data: ['po_id' => $po->id, 'supplier_id' => $supplier->id, 'tanggal_terima' => now()->toDateString(), 'no_faktur_supplier' => null],
            items: $itemPayload,
            userId: $user->id,
        );

        $this->assertSame(PurchaseOrder::STATUS_SELESAI, $po->fresh()->status);

        $this->expectException(\InvalidArgumentException::class);

        app(PenerimaanBarangService::class)->receive(
            data: ['po_id' => $po->id, 'supplier_id' => $supplier->id, 'tanggal_terima' => now()->toDateString(), 'no_faktur_supplier' => null],
            items: $itemPayload,
            userId: $user->id,
        );
    }

    public function test_po_yang_sudah_diterima_sebagian_tidak_bisa_dibatalkan(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();

        $po = app(PurchaseOrderService::class)->create(
            data: ['supplier_id' => $supplier->id, 'tanggal_po' => now()->toDateString()],
            items: [['obat_id' => $obat->id, 'jumlah_order' => 100, 'harga_satuan' => 9000]],
            userId: $user->id,
        );

        app(PenerimaanBarangService::class)->receive(
            data: ['po_id' => $po->id, 'supplier_id' => $supplier->id, 'tanggal_terima' => now()->toDateString(), 'no_faktur_supplier' => null],
            items: [[
                'obat_id' => $obat->id,
                'no_batch' => 'BATCH-PARTIAL',
                'tanggal_produksi' => null,
                'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
                'harga_beli' => 9000,
                'jumlah' => 20,
            ]],
            userId: $user->id,
        );

        $this->expectException(\InvalidArgumentException::class);

        app(PurchaseOrderService::class)->batalkan($po->fresh());
    }
}
