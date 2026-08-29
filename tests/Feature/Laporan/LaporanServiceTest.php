<?php

namespace Tests\Feature\Laporan;

use App\Models\BatchObat;
use App\Models\DetailTransaksi;
use App\Models\Obat;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use App\Services\LaporanKeuanganService;
use App\Services\LaporanPenjualanService;
use App\Services\LaporanStokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaporanServiceTest extends TestCase
{
    use RefreshDatabase;

    private function buatTransaksiSelesai(Obat $obat, BatchObat $batch, int $jumlah, float $hargaSatuan): TransaksiPenjualan
    {
        static $counter = 1;

        $kasir = User::factory()->create();

        $transaksi = TransaksiPenjualan::create([
            'no_transaksi' => 'TRX-TEST-' . str_pad((string) $counter++, 4, '0', STR_PAD_LEFT),
            'kasir_id' => $kasir->id,
            'total' => $jumlah * $hargaSatuan,
            'metode_bayar' => 'tunai',
            'jumlah_bayar' => $jumlah * $hargaSatuan,
            'kembalian' => 0,
            'status' => TransaksiPenjualan::STATUS_SELESAI,
        ]);

        DetailTransaksi::create([
            'transaksi_id' => $transaksi->id,
            'obat_id' => $obat->id,
            'batch_id' => $batch->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $hargaSatuan,
            'subtotal' => $jumlah * $hargaSatuan,
        ]);

        return $transaksi;
    }

    public function test_ringkasan_penjualan_mengecualikan_transaksi_dibatalkan(): void
    {
        $obat = Obat::factory()->create();
        $batch = BatchObat::factory()->for($obat)->create(['harga_beli' => 3000, 'harga_jual' => 5000]);

        $this->buatTransaksiSelesai($obat, $batch, 2, 5000); // omzet 10000
        $dibatalkan = $this->buatTransaksiSelesai($obat, $batch, 1, 5000);
        $dibatalkan->update(['status' => TransaksiPenjualan::STATUS_DIBATALKAN]);

        $ringkasan = app(LaporanPenjualanService::class)->ringkasan(now()->startOfDay(), now()->endOfDay());

        $this->assertSame(1, $ringkasan['total_transaksi']);
        $this->assertEqualsWithDelta(10000.0, $ringkasan['total_omzet'], 0.01);
    }

    public function test_obat_terlaris_terurut_dari_jumlah_terbanyak(): void
    {
        $obatA = Obat::factory()->create(['nama_obat' => 'Obat A']);
        $batchA = BatchObat::factory()->for($obatA)->create(['harga_beli' => 1000, 'harga_jual' => 2000]);
        $obatB = Obat::factory()->create(['nama_obat' => 'Obat B']);
        $batchB = BatchObat::factory()->for($obatB)->create(['harga_beli' => 1000, 'harga_jual' => 2000]);

        $this->buatTransaksiSelesai($obatA, $batchA, 3, 2000);
        $this->buatTransaksiSelesai($obatB, $batchB, 10, 2000);

        $terlaris = app(LaporanPenjualanService::class)->obatTerlaris(now()->startOfDay(), now()->endOfDay());

        $this->assertSame('Obat B', $terlaris->first()['nama_obat']);
        $this->assertSame(10, $terlaris->first()['jumlah_terjual']);
    }

    public function test_laporan_keuangan_menghitung_hpp_dan_margin(): void
    {
        $obat = Obat::factory()->create();
        $batch = BatchObat::factory()->for($obat)->create(['harga_beli' => 3000, 'harga_jual' => 5000]);

        $this->buatTransaksiSelesai($obat, $batch, 10, 5000); // omzet 50000, hpp 30000

        $ringkasan = app(LaporanKeuanganService::class)->ringkasan(now()->startOfDay(), now()->endOfDay());

        $this->assertEqualsWithDelta(50000.0, $ringkasan['omzet'], 0.01);
        $this->assertEqualsWithDelta(30000.0, $ringkasan['hpp'], 0.01);
        $this->assertEqualsWithDelta(20000.0, $ringkasan['laba_kotor'], 0.01);
        $this->assertEqualsWithDelta(40.0, $ringkasan['margin_persen'], 0.01);
    }

    public function test_stok_saat_ini_menandai_obat_menipis(): void
    {
        $obatMenipis = Obat::factory()->create(['stok_minimum' => 20]);
        BatchObat::factory()->for($obatMenipis)->create(['stok_saat_ini' => 5, 'stok_awal' => 5]);

        $obatAman = Obat::factory()->create(['stok_minimum' => 5]);
        BatchObat::factory()->for($obatAman)->create(['stok_saat_ini' => 50, 'stok_awal' => 50]);

        $service = app(LaporanStokService::class);

        $semuaStok = $service->stokSaatIni();
        $hanyaMenipis = $service->stokSaatIni(hanyaMenipis: true);

        $this->assertCount(2, $semuaStok);
        $this->assertCount(1, $hanyaMenipis);
        $this->assertSame($obatMenipis->id, $hanyaMenipis->first()['obat_id']);
    }

    public function test_batch_mendekati_kadaluarsa_dalam_rentang_hari(): void
    {
        $obat = Obat::factory()->create();

        BatchObat::factory()->for($obat)->create([
            'stok_saat_ini' => 10, 'stok_awal' => 10,
            'tanggal_kadaluarsa' => now()->addDays(15),
        ]);
        BatchObat::factory()->for($obat)->create([
            'stok_saat_ini' => 10, 'stok_awal' => 10,
            'tanggal_kadaluarsa' => now()->addDays(200),
        ]);

        $result = app(LaporanStokService::class)->batchMendekatiKadaluarsa(30);

        $this->assertCount(1, $result);
    }
}
