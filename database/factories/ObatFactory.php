<?php

namespace Database\Factories;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Satuan;
use Illuminate\Database\Eloquent\Factories\Factory;

class ObatFactory extends Factory
{
    protected $model = Obat::class;

    public function definition(): array
    {
        static $counter = 1;

        return [
            'kode_obat' => 'OBT-' . str_pad((string) $counter++, 6, '0', STR_PAD_LEFT),
            'nama_obat' => $this->faker->unique()->words(2, true),
            'nama_generik' => $this->faker->optional()->words(2, true),
            'kategori_id' => KategoriObat::factory(),
            'golongan' => $this->faker->randomElement(Obat::GOLONGAN_OPTIONS),
            'satuan_dasar_id' => Satuan::factory(),
            'barcode' => $this->faker->optional()->ean13(),
            'butuh_resep' => $this->faker->boolean(30),
            'deskripsi' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
