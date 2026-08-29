<?php

namespace Tests\Feature\MasterData;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Satuan;
use App\Services\ObatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ObatTest extends TestCase
{
    use RefreshDatabase;

    private function baseData(array $overrides = []): array
    {
        $kategori = KategoriObat::factory()->create();
        $satuanDasar = Satuan::factory()->create();

        return array_merge([
            'nama_obat' => 'Obat Uji',
            'nama_generik' => 'Generik Uji',
            'kategori_id' => $kategori->id,
            'golongan' => Obat::GOLONGAN_BEBAS,
            'satuan_dasar_id' => $satuanDasar->id,
            'barcode' => null,
            'butuh_resep' => false,
            'is_active' => true,
        ], $overrides);
    }

    public function test_creating_obat_generates_sequential_kode_obat(): void
    {
        $service = app(ObatService::class);
        $satuan = Satuan::factory()->create();

        $obat1 = $service->create(
            $this->baseData(),
            [['satuan_id' => $satuan->id, 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true]]
        );

        $obat2 = $service->create(
            $this->baseData(),
            [['satuan_id' => $satuan->id, 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true]]
        );

        $this->assertSame('OBT-000001', $obat1->kode_obat);
        $this->assertSame('OBT-000002', $obat2->kode_obat);
    }

    public function test_create_requires_exactly_one_satuan_dasar(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = app(ObatService::class);
        $satuanA = Satuan::factory()->create();
        $satuanB = Satuan::factory()->create();

        $service->create(
            $this->baseData(),
            [
                ['satuan_id' => $satuanA->id, 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true],
                ['satuan_id' => $satuanB->id, 'konversi_ke_satuan_dasar' => 10, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => false],
            ]
        );
    }

    public function test_create_persists_unit_conversions(): void
    {
        $service = app(ObatService::class);
        $tablet = Satuan::factory()->create(['nama' => 'Tablet']);
        $strip = Satuan::factory()->create(['nama' => 'Strip']);

        $obat = $service->create(
            $this->baseData(['satuan_dasar_id' => $tablet->id]),
            [
                ['satuan_id' => $tablet->id, 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true],
                ['satuan_id' => $strip->id, 'konversi_ke_satuan_dasar' => 10, 'is_satuan_dasar' => false, 'is_satuan_jual_default' => false],
            ]
        );

        $this->assertCount(2, $obat->obatSatuan);
        $this->assertDatabaseHas('obat_satuan', [
            'obat_id' => $obat->id,
            'satuan_id' => $strip->id,
            'konversi_ke_satuan_dasar' => 10,
        ]);
    }

    public function test_update_replaces_satuan_conversions_without_regenerating_kode_obat(): void
    {
        $service = app(ObatService::class);
        $satuan = Satuan::factory()->create();
        $newSatuan = Satuan::factory()->create();

        $obat = $service->create(
            $this->baseData(),
            [['satuan_id' => $satuan->id, 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true]]
        );

        $originalKode = $obat->kode_obat;

        $updated = $service->update(
            $obat,
            array_merge($this->baseData(), ['kode_obat' => 'SHOULD-NOT-APPLY']),
            [['satuan_id' => $newSatuan->id, 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true]]
        );

        $this->assertSame($originalKode, $updated->kode_obat);
        $this->assertCount(1, $updated->obatSatuan);
        $this->assertSame($newSatuan->id, $updated->obatSatuan->first()->satuan_id);
    }

    public function test_barcode_must_be_unique_when_present(): void
    {
        Obat::factory()->create(['barcode' => '1234567890123']);

        $response = $this->postJson(route('obat.store'), array_merge(
            $this->baseData(['barcode' => '1234567890123']),
            ['satuan' => [['satuan_id' => Satuan::factory()->create()->id, 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true]]]
        ));

        $response->assertSessionHasErrors('barcode');
    }

    public function test_deactivate_sets_is_active_false(): void
    {
        $obat = Obat::factory()->create(['is_active' => true]);

        app(ObatService::class)->deactivate($obat);

        $this->assertFalse($obat->fresh()->is_active);
    }
}
