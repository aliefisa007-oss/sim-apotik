<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Semua Livewire full-page component (Obat, Supplier, Laporan, dst)
        // sebelumnya diam-diam pakai layout minimal
        // resources/views/components/layouts/app.blade.php (tanpa menu
        // navigasi sama sekali) karena itu default package Livewire.
        // Diarahkan ke layouts.app (layout Breeze, sudah berisi
        // navigasi lengkap semua modul) supaya konsisten dengan
        // dashboard.blade.php & profile/edit.blade.php.
        Config::set('livewire.layout', 'layouts.app');
    }
}
