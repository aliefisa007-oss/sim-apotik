<?php

namespace App\Policies;

use App\Models\User;

/**
 * Bukan Policy per-model (tidak ada model Laporan) — dipakai manual via
 * Gate::forUser()/$user->can('viewLaporan') atau dipanggil langsung di
 * mount() Livewire (lihat Dashboard/Index, Laporan/*).
 *
 * KEPUTUSAN: dashboard & seluruh laporan (penjualan/stok/keuangan)
 * dibatasi owner & admin — TO BE VERIFIED apakah kasir/apoteker perlu
 * akses terbatas (mis. kasir lihat laporan penjualan miliknya sendiri).
 */
class LaporanPolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole('owner') || $user->hasRole('admin');
    }
}
