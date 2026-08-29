<?php

namespace Database\Factories;

use App\Models\Satuan;
use Illuminate\Database\Eloquent\Factories\Factory;

class SatuanFactory extends Factory
{
    protected $model = Satuan::class;

    public function definition(): array
    {
        return [
            'nama' => $this->faker->unique()->randomElement([
                'Tablet', 'Kapsul', 'Strip', 'Box', 'Botol', 'Ampul', 'Vial', 'Tube', 'Sachet',
            ]),
            'is_active' => true,
        ];
    }
}
