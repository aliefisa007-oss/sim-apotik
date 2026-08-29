<?php

namespace Database\Factories;

use App\Models\KategoriObat;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriObatFactory extends Factory
{
    protected $model = KategoriObat::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->unique()->randomElement([
                'Analgesik', 'Antibiotik', 'Antihistamin', 'Vitamin & Suplemen',
                'Obat Batuk & Flu', 'Obat Lambung', 'Antiseptik', 'Alat Kesehatan',
            ]) . ' ' . $this->faker->unique()->numerify('##'),
            'parent_id' => null,
            'is_active' => true,
        ];
    }
}
