<?php

namespace App\Policies;

use App\Models\HjaConfig;
use App\Models\User;

class HjaConfigPolicy
{
    /**
     * Konfigurasi HJA sengaja lebih ketat dari policy master data lain
     * (Owner-only, tidak termasuk Admin) — ini mengubah margin bisnis semua
     * obat sekaligus, dampaknya lebih besar dari CRUD master data biasa.
     * Sesuaikan jika kamu ingin Admin juga bisa mengubahnya.
     */
    public function update(User $user): bool
    {
        return $user->hasRole('owner');
    }
}
