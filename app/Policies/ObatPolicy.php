<?php

namespace App\Policies;

use App\Models\Obat;
use App\Models\User;

class ObatPolicy
{
    /**
     * Phase 1: only Owner/Admin can write master data.
     * Kasir/Apoteker/Gudang get read access via viewAny/view only.
     * Extend per-role write access here in later phases if needed —
     * this is the single place that rule should live (master prompt §54).
     */
    public function viewAny(User $user): bool
    {
        return true; // all authenticated roles may browse obat
    }

    public function view(User $user, Obat $obat): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }

    public function update(User $user, Obat $obat): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }

    public function deactivate(User $user, Obat $obat): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }
}
