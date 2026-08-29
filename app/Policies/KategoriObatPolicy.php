<?php

namespace App\Policies;

use App\Models\KategoriObat;
use App\Models\User;

class KategoriObatPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }

    public function update(User $user, KategoriObat $kategoriObat): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }
}
