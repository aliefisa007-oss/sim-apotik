<?php

namespace App\Policies;

use App\Models\Satuan;
use App\Models\User;

class SatuanPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }

    public function update(User $user, Satuan $satuan): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }
}
