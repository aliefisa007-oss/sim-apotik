<?php

namespace Database\Factories;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class BatchObatFactory extends Factory
{
    protected $model = BatchObat::class;

    public function definition(): array
    {
        $stok = $this->faker->numberBetween(10, 200);

        return [
            'obat_id' => Obat::factory(),
            'supplier_id' => Supplier::factory(),
            'no_batch' => strtoupper($this->faker->unique()->bothify('BATCH-####??')),
            'tanggal_produksi' => now()->subMonths(6)->toDateString(),
            'tanggal_kadaluarsa' => now()->addMonths(12)->toDateString(),
            'harga_beli' => $this->faker->randomFloat(2, 1000, 50000),
            'harga_jual' => null,
            'stok_awal' => $stok,
            'stok_saat_ini' => $stok,
            'status' => BatchObat::STATUS_AKTIF,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'tanggal_kadaluarsa' => now()->subDays(5)->toDateString(),
        ]);
    }

    public function expiringInDays(int $days): static
    {
        return $this->state(fn () => [
            'tanggal_kadaluarsa' => now()->addDays($days)->toDateString(),
        ]);
    }
}
