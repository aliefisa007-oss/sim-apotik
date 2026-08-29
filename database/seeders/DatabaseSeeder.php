<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    $this->call([
        RoleSeeder::class,
        UserSeeder::class,
        // seeder lain yang sudah ada, taruh setelah ini
    ]);
}
}