<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Role dasar yang diasumsikan dipakai di seluruh Policy sejak Phase 1
 * (lihat catatan "asumsi belum diverifikasi" di handoff project) —
 * owner, admin, kasir, apoteker, gudang. Safe to re-run (firstOrCreate).
 *
 * WAJIB dijalankan sebelum test/pemakaian apapun yang melibatkan
 * hasRole()/assignRole(), termasuk verifikasi resep Phase 6 (§ResepPolicy).
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['owner', 'admin', 'kasir', 'apoteker', 'gudang'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
