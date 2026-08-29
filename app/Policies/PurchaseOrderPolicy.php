<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;

class PurchaseOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('gudang');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('gudang');
    }

    public function update(User $user, PurchaseOrder $purchaseOrder): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('gudang');
    }
}
