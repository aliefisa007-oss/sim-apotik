<?php

namespace App\Policies;

use App\Models\BatchObat;
use App\Models\User;

class BatchObatPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BatchObat $batchObat): bool
    {
        return true;
    }

    /**
     * Dipakai juga untuk gate Stok Masuk & Penyesuaian (lihat komponen
     * Livewire terkait) — bukan hanya untuk create BatchObat murni.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('gudang');
    }

    /**
     * Dipakai oleh Kalkulator HJA untuk menentukan siapa yang boleh
     * mengubah harga_jual sebuah batch (bukan mengubah data batch itu
     * sendiri, hanya harga jualnya).
     */
    public function update(User $user, BatchObat $batchObat): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }
}
