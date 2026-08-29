<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        static $counter = 1;

        return [
            'no_po' => 'PO-' . now()->format('Ymd') . '-' . str_pad((string) $counter++, 4, '0', STR_PAD_LEFT),
            'supplier_id' => Supplier::factory(),
            'tanggal_po' => now()->toDateString(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'total' => 0,
            'user_id' => User::factory(),
        ];
    }
}
