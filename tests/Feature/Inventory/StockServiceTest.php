<?php

namespace Tests\Feature\Inventory;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_receive_stock_membuat_batch_baru_dan_kartu_stok(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();

        $batch = app(StockService::class)->receiveStock([
            'obat_id' => $obat->id,
            'supplier_id' => $supplier->id,
            'no_batch' => 'BATCH-001',
            'tanggal_produksi' => now()->subMonth()->toDateString(),
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'harga_beli' => 15000,
            'jumlah' => 100,
        ], $user->id);

        $this->assertSame(100, $batch->stok_saat_ini);
        $this->assertDatabaseHas('kartu_stok', [
            'batch_id' => $batch->id,
            'jenis_transaksi' => 'masuk_pembelian',
            'jumlah' => 100,
            'saldo_sebelum' => 0,
            'saldo_sesudah' => 100,
        ]);
    }

    public function test_receive_stock_batch_sama_menambah_stok_bukan_duplikat_baris(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();
        $service = app(StockService::class);

        $payload = [
            'obat_id' => $obat->id,
            'supplier_id' => $supplier->id,
            'no_batch' => 'BATCH-001',
            'tanggal_produksi' => null,
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'harga_beli' => 15000,
            'jumlah' => 50,
        ];

        $batch1 = $service->receiveStock($payload, $user->id);
        $payload['jumlah'] = 30;
        $batch2 = $service->receiveStock($payload, $user->id);

        $this->assertSame($batch1->id, $batch2->id);
        $this->assertSame(80, $batch2->stok_saat_ini);
        $this->assertSame(1, BatchObat::where('obat_id', $obat->id)->count());
    }

    public function test_receive_stock_batch_sama_dengan_harga_beli_berbeda_mengikuti_harga_terakhir(): void
    {
        $obat = Obat::factory()->create();
        $supplier = Supplier::factory()->create();
        $user = User::factory()->create();
        $service = app(StockService::class);

        $payload = [
            'obat_id' => $obat->id,
            'supplier_id' => $supplier->id,
            'no_batch' => 'BATCH-001',
            'tanggal_produksi' => null,
            'tanggal_kadaluarsa' => now()->addYear()->toDateString(),
            'harga_beli' => 15000,
            'jumlah' => 50,
        ];

        $service->receiveStock($payload, $user->id);

        $payload['harga_beli'] = 17500;
        $payload['jumlah'] = 20;
        $batch = $service->receiveStock($payload, $user->id);

        $this->assertEquals(17500, $batch->harga_beli);
    }

    public function test_adjust_stock_mencatat_selisih_dengan_benar(): void
    {
        $obat = Obat::factory()->create();
        $user = User::factory()->create();
        $batch = BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 100, 'stok_awal' => 100]);

        app(StockService::class)->adjustStock($batch->id, 92, $user->id, 'Hasil stok opname bulanan');

        $this->assertSame(92, $batch->fresh()->stok_saat_ini);
        $this->assertDatabaseHas('kartu_stok', [
            'batch_id' => $batch->id,
            'jenis_transaksi' => 'penyesuaian',
            'jumlah' => -8,
            'saldo_sebelum' => 100,
            'saldo_sesudah' => 92,
            'keterangan' => 'Hasil stok opname bulanan',
        ]);
    }

    public function test_write_off_expired_mengosongkan_stok_dan_set_status_expired(): void
    {
        $obat = Obat::factory()->create();
        $user = User::factory()->create();
        $batch = BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 15, 'stok_awal' => 15]);

        app(StockService::class)->writeOffExpired($batch->id, $user->id);

        $fresh = $batch->fresh();
        $this->assertSame(0, $fresh->stok_saat_ini);
        $this->assertSame('expired', $fresh->status);
        $this->assertDatabaseHas('kartu_stok', [
            'batch_id' => $batch->id,
            'jenis_transaksi' => 'expired_writeoff',
            'jumlah' => -15,
        ]);
    }

    public function test_deduct_gagal_jika_stok_batch_tidak_cukup(): void
    {
        $obat = Obat::factory()->create();
        $user = User::factory()->create();
        $batch = BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 3, 'stok_awal' => 3]);

        $this->expectException(\App\Exceptions\InsufficientStockException::class);

        app(StockService::class)->deductFromBatch($batch->id, 5, 'keluar_jual', $user->id);
    }
}
