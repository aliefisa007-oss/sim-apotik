<?php

namespace Tests\Feature\Penjualan;

use App\Exceptions\ResepMismatchException;
use App\Exceptions\ResepNotVerifiedException;
use App\Models\BatchObat;
use App\Models\DetailResep;
use App\Models\Obat;
use App\Models\Resep;
use App\Models\User;
use App\Services\PenjualanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenjualanResepFlowTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedResepWithGolonganKeras(int $diresepkan = 10): array
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_KERAS]);
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 100, 'stok_awal' => 100, 'harga_jual' => 5000]);

        $apoteker = User::factory()->create();
        $apoteker->assignRole('apoteker');

        $resep = Resep::factory()->terverifikasi()->create(['apoteker_verifikasi_id' => $apoteker->id]);
        DetailResep::create([
            'resep_id' => $resep->id,
            'obat_id' => $obat->id,
            'jumlah_diresepkan' => $diresepkan,
            'jumlah_terlayani' => 0,
        ]);

        return [$resep, $obat, $apoteker];
    }

    public function test_sale_via_resep_terverifikasi_auto_derives_apoteker_approval(): void
    {
        [$resep, $obat, $apoteker] = $this->verifiedResepWithGolonganKeras();
        $kasir = User::factory()->create();

        $transaksi = app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 5]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 25000,
            resepId: $resep->id,
        );

        $this->assertSame($apoteker->id, $transaksi->apoteker_approval_id);
        $this->assertSame($resep->id, $transaksi->resep_id);
        $this->assertSame($resep->pasien_id, $transaksi->pasien_id);
    }

    public function test_dispensing_penuh_menandai_resep_selesai(): void
    {
        [$resep, $obat] = $this->verifiedResepWithGolonganKeras(diresepkan: 5);
        $kasir = User::factory()->create();

        app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 5]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 25000,
            resepId: $resep->id,
        );

        $this->assertSame(Resep::STATUS_SELESAI, $resep->fresh()->status);
        $this->assertSame(5, $resep->detail()->first()->jumlah_terlayani);
    }

    public function test_dispensing_sebagian_resep_tetap_terverifikasi(): void
    {
        [$resep, $obat] = $this->verifiedResepWithGolonganKeras(diresepkan: 10);
        $kasir = User::factory()->create();

        app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 4]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 20000,
            resepId: $resep->id,
        );

        $this->assertSame(Resep::STATUS_TERVERIFIKASI, $resep->fresh()->status);
        $this->assertSame(4, $resep->detail()->first()->jumlah_terlayani);
    }

    public function test_resep_belum_diverifikasi_ditolak(): void
    {
        $obat = Obat::factory()->create(['golongan' => Obat::GOLONGAN_KERAS]);
        BatchObat::factory()->for($obat)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => 5000]);
        $resep = Resep::factory()->create(); // status default: menunggu_verifikasi
        DetailResep::create(['resep_id' => $resep->id, 'obat_id' => $obat->id, 'jumlah_diresepkan' => 5]);
        $kasir = User::factory()->create();

        $this->expectException(ResepNotVerifiedException::class);

        app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 1]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 5000,
            resepId: $resep->id,
        );
    }

    public function test_item_golongan_restricted_di_luar_resep_ditolak(): void
    {
        [$resep] = $this->verifiedResepWithGolonganKeras();

        $obatLain = Obat::factory()->create(['golongan' => Obat::GOLONGAN_NARKOTIKA]);
        BatchObat::factory()->for($obatLain)->create(['stok_saat_ini' => 10, 'stok_awal' => 10, 'harga_jual' => 5000]);
        $kasir = User::factory()->create();

        $this->expectException(ResepMismatchException::class);

        app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obatLain->id, 'jumlah' => 1]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 5000,
            resepId: $resep->id,
        );
    }

    public function test_jumlah_melebihi_sisa_resep_ditolak(): void
    {
        [$resep, $obat] = $this->verifiedResepWithGolonganKeras(diresepkan: 5);
        $kasir = User::factory()->create();

        $this->expectException(ResepMismatchException::class);

        app(PenjualanService::class)->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 6]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 30000,
            resepId: $resep->id,
        );
    }

    public function test_void_transaksi_resep_mengembalikan_jumlah_terlayani_dan_status_resep(): void
    {
        [$resep, $obat] = $this->verifiedResepWithGolonganKeras(diresepkan: 5);
        $kasir = User::factory()->create();
        $service = app(PenjualanService::class);

        $transaksi = $service->createSale(
            items: [['obat_id' => $obat->id, 'jumlah' => 5]],
            metodeBayar: 'tunai',
            kasirId: $kasir->id,
            jumlahBayar: 25000,
            resepId: $resep->id,
        );

        $this->assertSame(Resep::STATUS_SELESAI, $resep->fresh()->status);

        $service->voidSale($transaksi, $kasir->id, 'Salah dispensing');

        $this->assertSame(Resep::STATUS_TERVERIFIKASI, $resep->fresh()->status);
        $this->assertSame(0, $resep->detail()->first()->jumlah_terlayani);
    }
}
