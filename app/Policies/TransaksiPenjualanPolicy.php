<?php

namespace App\Policies;

use App\Models\TransaksiPenjualan;
use App\Models\User;

class TransaksiPenjualanPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('kasir') || $user->hasRole('owner') || $user->hasRole('admin');
    }

    public function view(User $user, TransaksiPenjualan $transaksi): bool
    {
        return $user->hasRole('owner')
            || $user->hasRole('admin')
            || $transaksi->kasir_id === $user->id;
    }

    /**
     * Void sengaja dibatasi Owner/Admin, TIDAK kasir sendiri — mencegah
     * kasir menutupi kesalahan/kecurangan dengan membatalkan transaksinya
     * sendiri tanpa jejak persetujuan pihak lain.
     */
    public function void(User $user, TransaksiPenjualan $transaksi): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }
}
