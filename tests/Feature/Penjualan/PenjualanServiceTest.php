<?php

namespace Tests\Feature\Penjualan;

use App\Exceptions\ApprovalRequiredException;
use App\Exceptions\HargaJualBelumDiaturException;
use App\Exceptions\InsufficientStockException;
use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use App\Services\PenjualanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_obat_bebas_tidak_membutuhkan_approval(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_BEBAS]);
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => 5000]);
        $kasir = User::factory()->create();

        $transaksi = app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 2]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 10000,
        );

        $this->assertSame('selesai', $transaksi->status);
        $this->assertEquals(10000.0, $transaksi->total);
    }

    public function test_obat_keras_tanpa_approval_ditolak(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_KERAS]);
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => 5000]);
        $kasir = User::factory()->create();

        $this->expectException(ApprovalRequiredException::class);

        app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 1]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 5000,
        );
    }

    public function test_obat_keras_dengan_apoteker_valid_berhasil(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_NARKOTIKA]);
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => 5000]);
        $kasir = User::factory()->create();
        $apoteker = User::factory()->create();
        $apoteker->assignRole('apoteker'); // asumsi helper role tersedia di model User

        $transaksi = app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 1]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 5000,
            apotekerApprovalId: $apoteker->id,
        );

        $this->assertSame($apoteker->id, $transaksi->apoteker_approval_id);
    }

    public function test_split_dua_batch_menghasilkan_dua_baris_detail_dengan_harga_masing_masing(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_BEBAS]);
        $batchMurah = BatchObat::factory()->for($obat)->expiringInDays(5)->create(['stok_saat_ini' => 5, 'stok_awal' => 5, 'harga_jual' => 5000]);
        $batchMahal = BatchObat::factory()->for($obat)->expiringInDays(15)->create(['stok_saat_ini' => 50, 'stok_awal' => 50, 'harga_jual' => 6000]);
        $kasir = User::factory()->create();

        $transaksi = app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 10]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 100000,
        );

        $this->assertCount(2, $transaksi->detail);
        // 5 dari batchMurah @5000 + 5 dari batchMahal @6000 = 25.000 + 30.000
        $this->assertEquals(55000.0, $transaksi->total);
        $this->assertSame(0, $batchMurah->fresh()->stok_saat_ini);
        $this->assertSame(45, $batchMahal->fresh()->stok_saat_ini);
    }

    public function test_stok_tidak_cukup_rollback_tidak_ada_transaksi_tersimpan(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_BEBAS]);
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 2, 'stok_awal' => 2, 'harga_jual' => 5000]);
        $kasir = User::factory()->create();

        try {
            app(PenjualanService::class)->createSale(
                items: [['obat_id' => $obat->id, 'jumlah' => 10]],
                metodeBayar: 'tunai',
                kasirId: $kasir->id,
                jumlahBayar: 100000,
            );
        } catch (InsufficientStockException) {
            // expected
        }

        $this->assertSame(0, TransaksiPenjualan::count());
        $this->assertSame(2, BatchObat::where('obat_id', $obat->id)->first()->stok_saat_ini);
    }

    public function test_harga_jual_belum_diatur_ditolak(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_BEBAS]);
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => null]);
        $kasir = User::factory()->create();

        $this->expectException(HargaJualBelumDiaturException::class);

        app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 1]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 5000,
        );
    }

    public function test_kartu_stok_tercatat_dengan_referensi_transaksi(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_BEBAS]);
        $batch = BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => 5000]);
        $kasir = User::factory()->create();

        $transaksi = app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 3]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 15000,
        );

        $this->assertDatabaseHas('kartu_stok', [
            'batch_id' => $batch->id,
            'jenis_transaksi' => 'keluar_jual',
            'jumlah' => -3,
            'referensi_type' => TransaksiPenjualan::class,
            'referensi_id' => $transaksi->id,
        ]);
    }

    public function test_void_mengembalikan_stok_dan_tidak_menghapus_transaksi(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_BEBAS]);
        $batch = BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => 5000]);
        $kasir = User::factory()->create();
        $service = app(PenjualanService::class);

        $transaksi = $service->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 4]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 20000,
        );

        $this->assertSame(6, $batch->fresh()->stok_saat_ini);

        $voided = $service->voidSale($transaksi, $kasir->id, 'Salah input obat');

        $this->assertSame('dibatalkan', $voided->status);
        $this->assertSame(10, $batch->fresh()->stok_saat_ini);
        $this->assertDatabaseHas('transaksi_penjualan', ['id' => $transaksi->id, 'status' => 'dibatalkan']);
        // Bukti tidak pernah delete:
        $this->assertDatabaseCount('transaksi_penjualan', 1);
    }
}