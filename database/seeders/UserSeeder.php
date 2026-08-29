<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Akun development untuk testing login per role. Password sama untuk semua
 * (password) — HANYA untuk lingkungan lokal/testing, JANGAN dipakai di
 * production. Safe to re-run: firstOrCreate by email.
 *
 * WAJIB dijalankan setelah RoleSeeder (assignRole butuh role sudah ada).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Owner Apotik', 'email' => 'owner@sim-apotik.test', 'role' => 'owner'],
            ['name' => 'Admin Apotik', 'email' => 'admin@sim-apotik.test', 'role' => 'admin'],
            ['name' => 'Kasir Apotik', 'email' => 'kasir@sim-apotik.test', 'role' => 'kasir'],
            ['name' => 'Apoteker Apotik', 'email' => 'apoteker@sim-apotik.test', 'role' => 'apoteker'],
            ['name' => 'Gudang Apotik', 'email' => 'gudang@sim-apotik.test', 'role' => 'gudang'],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => Hash::make('password')]
            );

            if (!$user->hasRole($account['role'])) {
                $user->assignRole($account['role']);
            }
        }
    }
}
