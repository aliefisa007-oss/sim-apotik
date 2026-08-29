<?php

namespace App\Policies;

use App\Models\Pasien;
use App\Models\User;

class PasienPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // semua role bisa cari pasien (dibutuhkan kasir & apoteker)
    }

    public function view(User $user, Pasien $pasien): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('kasir') || $user->hasRole('apoteker');
    }

    public function update(User $user, Pasien $pasien): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('kasir') || $user->hasRole('apoteker');
    }

    public function deactivate(User $user, Pasien $pasien): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }
}
