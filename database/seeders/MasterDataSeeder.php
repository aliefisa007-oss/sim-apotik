<?php

namespace Database\Seeders;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\ObatSatuan;
use App\Models\Satuan;
use App\Models\Supplier;
use App\Services\ObatService;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Dummy development data only — no real patient/pharmacy data.
     * Safe to re-run: uses firstOrCreate for lookups, and only seeds
     * sample obat when the table is empty.
     */
    public function run(): void
    {
        $kategoriNames = ['Analgesik', 'Antibiotik', 'Antihistamin', 'Vitamin & Suplemen', 'Obat Batuk & Flu'];
        $kategoriMap = collect($kategoriNames)->mapWithKeys(function ($nama) {
            return [$nama => KategoriObat::firstOrCreate(['nama' => $nama])->id];
        });

        $satuanNames = ['Tablet', 'Strip', 'Box', 'Botol', 'Kapsul'];
        $satuanMap = collect($satuanNames)->mapWithKeys(function ($nama) {
            return [$nama => Satuan::firstOrCreate(['nama' => $nama])->id];
        });

        Supplier::firstOrCreate(
            ['no_izin_pbf' => 'PBF-0001-DUMMY'],
            [
                'nama_pbf' => 'PBF Sehat Sejahtera (Dummy)',
                'alamat' => 'Jl. Contoh No. 1, Jember',
                'kontak' => '0331-000000',
                'is_active' => true,
            ]
        );

        if (Obat::count() > 0) {
            return; // don't duplicate obat on re-run
        }

        $service = app(ObatService::class);

        $service->create(
            [
                'nama_obat' => 'Paracetamol 500mg',
                'nama_generik' => 'Paracetamol',
                'kategori_id' => $kategoriMap['Analgesik'],
                'golongan' => Obat::GOLONGAN_BEBAS,
                'satuan_dasar_id' => $satuanMap['Tablet'],
                'barcode' => null,
                'butuh_resep' => false,
                'is_active' => true,
            ],
            [
                ['satuan_id' => $satuanMap['Tablet'], 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true],
                ['satuan_id' => $satuanMap['Strip'], 'konversi_ke_satuan_dasar' => 10, 'is_satuan_dasar' => false, 'is_satuan_jual_default' => false],
                ['satuan_id' => $satuanMap['Box'], 'konversi_ke_satuan_dasar' => 100, 'is_satuan_dasar' => false, 'is_satuan_jual_default' => false],
            ]
        );

        $service->create(
            [
                'nama_obat' => 'Amoxicillin 500mg',
                'nama_generik' => 'Amoxicillin',
                'kategori_id' => $kategoriMap['Antibiotik'],
                'golongan' => Obat::GOLONGAN_KERAS,
                'satuan_dasar_id' => $satuanMap['Kapsul'],
                'barcode' => null,
                'butuh_resep' => true,
                'is_active' => true,
            ],
            [
                ['satuan_id' => $satuanMap['Kapsul'], 'konversi_ke_satuan_dasar' => 1, 'is_satuan_dasar' => true, 'is_satuan_jual_default' => true],
                ['satuan_id' => $satuanMap['Strip'], 'konversi_ke_satuan_dasar' => 10, 'is_satuan_dasar' => false, 'is_satuan_jual_default' => false],
            ]
        );
    }
}
