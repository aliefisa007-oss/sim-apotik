<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'nama_pbf' => 'PBF ' . $this->faker->unique()->company(),
            'no_izin_pbf' => strtoupper($this->faker->unique()->bothify('PBF-####-????')),
            'alamat' => $this->faker->address(),
            'kontak' => $this->faker->phoneNumber(),
            'is_active' => true,
        ];
    }
}
