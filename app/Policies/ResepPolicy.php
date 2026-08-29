<?php

namespace App\Policies;

use App\Models\Resep;
use App\Models\User;

class ResepPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Resep $resep): bool
    {
        return true;
    }

    /**
     * Input resep boleh oleh kasir/admin/owner/apoteker — biasanya kasir
     * yang menginput saat pasien datang bawa resep fisik.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('kasir') || $user->hasRole('apoteker');
    }

    /**
     * Verifikasi/tolak resep — HANYA apoteker (§19, wewenang klinis tidak
     * boleh didelegasikan ke kasir/admin sekalipun owner).
     */
    public function verify(User $user): bool
    {
        return $user->hasRole('apoteker');
    }
}
