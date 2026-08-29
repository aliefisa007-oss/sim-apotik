<?php

use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Laporan\Keuangan as LaporanKeuangan;
use App\Livewire\Laporan\Penjualan as LaporanPenjualan;
use App\Livewire\Laporan\Stok as LaporanStok;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/penjualan', LaporanPenjualan::class)->name('penjualan');
        Route::get('/stok', LaporanStok::class)->name('stok');
        Route::get('/keuangan', LaporanKeuangan::class)->name('keuangan');
    });
});
