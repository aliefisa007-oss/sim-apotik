<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Akun awal untuk development/testing lokal. Password sama untuk
     * semua akun demi kemudahan development — WAJIB diganti sebelum
     * dipakai di lingkungan produksi sungguhan.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Owner', 'email' => 'owner@apotik.test', 'role' => 'owner'],
            ['name' => 'Admin', 'email' => 'admin@apotik.test', 'role' => 'admin'],
            ['name' => 'Kasir', 'email' => 'kasir@apotik.test', 'role' => 'kasir'],
            ['name' => 'Gudang', 'email' => 'gudang@apotik.test', 'role' => 'gudang'],
            ['name' => 'Apoteker', 'email' => 'apoteker@apotik.test', 'role' => 'apoteker'],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );

            if (! $user->hasRole($account['role'])) {
                $user->assignRole($account['role']);
            }
        }
    }
}