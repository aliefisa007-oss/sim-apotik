<?php

namespace App\Policies;

use App\Models\User;

/**
 * Manajemen user/role dibatasi role 'owner' SAJA (bukan admin) — konsisten
 * dengan LaporanKeuanganPolicy. OPEN DECISION: jika admin juga perlu akses,
 * ini perlu diubah eksplisit, bukan diasumsikan.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('owner');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('owner');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasRole('owner');
    }

    /**
     * Nonaktifkan akun sendiri tidak diizinkan — mencegah owner terkunci
     * dari sistem tanpa akun owner aktif lain.
     */
    public function deactivate(User $user, User $target): bool
    {
        return $user->hasRole('owner') && $user->id !== $target->id;
    }
}
