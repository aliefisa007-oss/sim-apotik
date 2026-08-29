<?php

namespace Tests\Feature\Hja;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\User;
use App\Services\HJAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class HJAServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_markup_calculation(): void
    {
        $result = app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'diskon_persen' => 0,
            'tax_percent' => 0,
            'harga_termasuk_pajak' => true, // supaya pajak tidak ikut campur di tes ini
            'metode' => 'markup',
            'persen_markup_margin' => 20,
            'rounding_method' => 'round',
            'rounding_increment' => 1,
        ]);

        // Cost 10.000 x (1 + 20%) = 12.000
        $this->assertEquals(12000.0, $result['harga_final']);
    }

    public function test_margin_calculation(): void
    {
        $result = app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'diskon_persen' => 0,
            'tax_percent' => 0,
            'harga_termasuk_pajak' => true,
            'metode' => 'margin',
            'persen_markup_margin' => 20,
            'rounding_method' => 'round',
            'rounding_increment' => 1,
        ]);

        // Cost 10.000 / (1 - 20%) = 12.500
        $this->assertEquals(12500.0, $result['harga_final']);
    }

    public function test_tax_excluded_ditambahkan_ke_cost_basis(): void
    {
        $result = app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'diskon_persen' => 0,
            'tax_percent' => 11,
            'harga_termasuk_pajak' => false,
            'metode' => 'markup',
            'persen_markup_margin' => 0,
            'rounding_method' => 'round',
            'rounding_increment' => 1,
        ]);

        // Netto 10.000, pajak 11% = 1.100, cost basis 11.100, markup 0% -> final 11.100
        $this->assertEquals(1100.0, $result['pajak_nominal']);
        $this->assertEquals(11100.0, $result['harga_final']);
    }

    public function test_tax_included_tidak_menambah_pajak_lagi(): void
    {
        $result = app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'diskon_persen' => 0,
            'tax_percent' => 11,
            'harga_termasuk_pajak' => true,
            'metode' => 'markup',
            'persen_markup_margin' => 0,
            'rounding_method' => 'round',
            'rounding_increment' => 1,
        ]);

        // Tidak boleh double tax — pajak_nominal harus 0 walau tax_percent diisi.
        $this->assertEquals(0.0, $result['pajak_nominal']);
        $this->assertEquals(10000.0, $result['harga_final']);
    }

    public function test_diskon_mengurangi_harga_netto(): void
    {
        $result = app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'diskon_persen' => 10,
            'tax_percent' => 0,
            'harga_termasuk_pajak' => true,
            'metode' => 'markup',
            'persen_markup_margin' => 0,
            'rounding_method' => 'round',
            'rounding_increment' => 1,
        ]);

        // 10.000 x (1 - 10%) = 9.000
        $this->assertEquals(9000.0, $result['harga_netto']);
        $this->assertEquals(9000.0, $result['harga_final']);
    }

    public function test_pembulatan_round_up_ke_kelipatan(): void
    {
        $result = app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'diskon_persen' => 0,
            'tax_percent' => 0,
            'harga_termasuk_pajak' => true,
            'metode' => 'markup',
            'persen_markup_margin' => 13, // -> 11.300, harus naik ke 11.500 (kelipatan 500)
            'rounding_method' => 'round_up',
            'rounding_increment' => 500,
        ]);

        $this->assertEquals(11300.0, $result['harga_sebelum_pembulatan']);
        $this->assertEquals(11500.0, $result['harga_final']);
        $this->assertEquals(200.0, $result['rounding_difference']);
    }

    public function test_pembulatan_round_down_ke_kelipatan(): void
    {
        $result = app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'diskon_persen' => 0,
            'tax_percent' => 0,
            'harga_termasuk_pajak' => true,
            'metode' => 'markup',
            'persen_markup_margin' => 13,
            'rounding_method' => 'round_down',
            'rounding_increment' => 500,
        ]);

        $this->assertEquals(11000.0, $result['harga_final']);
    }

    public function test_margin_100_persen_ditolak_cegah_pembagian_nol(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'metode' => 'margin',
            'persen_markup_margin' => 100,
        ]);
    }

    public function test_rounding_increment_nol_ditolak(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(HJAService::class)->calculate([
            'harga_faktur' => 10000,
            'rounding_increment' => 0,
        ]);
    }

    public function test_set_harga_jual_batch_aware_dua_batch_beda_cost(): void
    {
        $obat = Obat::factory()->create();
        $user = User::factory()->create();
        $service = app(HJAService::class);

        $batchMurah = BatchObat::factory()->for($obat)->create(['harga_beli' => 10000]);
        $batchMahal = BatchObat::factory()->for($obat)->create(['harga_beli' => 12000]);

        $params = [
            'diskon_persen' => 0,
            'tax_percent' => 0,
            'harga_termasuk_pajak' => true,
            'metode' => 'markup',
            'persen_markup_margin' => 10,
            'rounding_method' => 'round',
            'rounding_increment' => 1,
        ];

        $service->setHargaJual($batchMurah, $params, $user->id);
        $service->setHargaJual($batchMahal, $params, $user->id);

        // Cost basis ambil dari harga_beli BATCH masing-masing, bukan satu
        // harga global — inilah inti dari "HJA berbasis batch" (§11).
        $this->assertEquals(11000.0, $batchMurah->fresh()->harga_jual);
        $this->assertEquals(13200.0, $batchMahal->fresh()->harga_jual);
    }

    public function test_set_harga_jual_mencatat_histori(): void
    {
        $obat = Obat::factory()->create();
        $user = User::factory()->create();
        $batch = BatchObat::factory()->for($obat)->create(['harga_beli' => 10000, 'harga_jual' => null]);

        app(HJAService::class)->setHargaJual($batch, [
            'metode' => 'markup',
            'persen_markup_margin' => 20,
            'tax_percent' => 0,
            'harga_termasuk_pajak' => true,
            'rounding_method' => 'round',
            'rounding_increment' => 1,
        ], $user->id, 'Setup harga awal');

        $this->assertDatabaseHas('histori_harga_obat', [
            'batch_id' => $batch->id,
            'harga_lama' => null,
            'harga_baru' => 12000.00,
            'alasan' => 'Setup harga awal',
            'user_id' => $user->id,
        ]);
    }
}
