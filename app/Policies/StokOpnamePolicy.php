<?php

namespace App\Policies;

use App\Models\StokOpname;
use App\Models\User;

class StokOpnamePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, StokOpname $stokOpname): bool
    {
        return true;
    }

    /**
     * Mulai sesi opname & catat hasil hitung fisik — sama dengan siapa yang
     * boleh mencatat Stok Masuk/Penyesuaian (BatchObatPolicy::create).
     */
    public function create(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('gudang');
    }

    /**
     * Selesaikan opname (memicu penyesuaian stok riil untuk semua item
     * selisih) sengaja DIBATASI Owner/Admin — dual control antara yang
     * menghitung fisik (bisa gudang) dan yang menyetujui hasilnya jadi
     * perubahan stok resmi. Konsisten dengan pola void transaksi penjualan
     * (TransaksiPenjualanPolicy::void).
     */
    public function finalize(User $user, StokOpname $stokOpname): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }

    /**
     * Batalkan sesi (belum berdampak ke stok riil) — sama dengan yang
     * boleh memulai, tidak perlu dual control seketat finalize.
     */
    public function cancel(User $user, StokOpname $stokOpname): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin') || $user->hasRole('gudang');
    }
}
