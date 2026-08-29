<?php

namespace Database\Factories;

use App\Models\Pasien;
use App\Models\Resep;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResepFactory extends Factory
{
    protected $model = Resep::class;

    public function definition(): array
    {
        static $counter = 1;

        return [
            'no_resep' => 'RSP-' . now()->format('Ymd') . '-' . str_pad((string) $counter++, 4, '0', STR_PAD_LEFT),
            'pasien_id' => Pasien::factory(),
            'nama_dokter' => 'dr. ' . $this->faker->name(),
            'no_sip_dokter' => $this->faker->optional()->bothify('SIP-########'),
            'tanggal_resep' => now()->toDateString(),
            'status' => Resep::STATUS_MENUNGGU_VERIFIKASI,
            'apoteker_verifikasi_id' => null,
            'catatan_verifikasi' => null,
            'verified_at' => null,
            'created_by' => User::factory(),
        ];
    }

    public function terverifikasi(): static
    {
        return $this->state(fn () => [
            'status' => Resep::STATUS_TERVERIFIKASI,
            'apoteker_verifikasi_id' => User::factory(),
            'verified_at' => now(),
        ]);
    }
}
