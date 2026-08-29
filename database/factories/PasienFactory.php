<?php

namespace Database\Factories;

use App\Models\Pasien;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasienFactory extends Factory
{
    protected $model = Pasien::class;

    public function definition(): array
    {
        return [
            'no_rm' => $this->faker->optional()->unique()->bothify('RM-######'),
            'nama_pasien' => $this->faker->name(),
            'tanggal_lahir' => $this->faker->optional()->date(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'alamat' => $this->faker->optional()->address(),
            'no_telepon' => $this->faker->optional()->phoneNumber(),
            'alergi' => null,
            'is_active' => true,
        ];
    }
}
