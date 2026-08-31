<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// '/' diarahkan langsung ke dashboard — ini aplikasi internal apotek,
// bukan produk dengan landing page publik, jadi welcome page bawaan
// starter kit Laravel tidak relevan di sini. Tamu (belum login) akan
// otomatis dilempar ke halaman login oleh middleware 'auth' di route
// dashboard (lihat routes/dashboard-laporan.php).
Route::get('/', fn () => redirect()->route('dashboard'));

// CATATAN: route 'dashboard' yang SEBENARNYA (Livewire DashboardIndex,
// hasil Phase 7) didaftarkan di routes/dashboard-laporan.php. Dulu di
// sini ada route '/dashboard' duplikat yang mengembalikan
// view('dashboard') statis — karena didaftarkan lebih dulu di
// RouteCollection, Laravel selalu men-serve versi statis itu untuk
// GET /dashboard, sehingga dashboard Livewire yang sebenarnya (dengan
// laporan & grafik) TIDAK PERNAH benar-benar terbuka lewat menu mana
// pun. Route duplikat itu sudah dihapus di sini.

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/dashboard-laporan.php';
require __DIR__.'/hja.php';
require __DIR__.'/inventory.php';
require __DIR__.'/master-data.php';
require __DIR__.'/pengguna.php';
require __DIR__.'/penjualan.php';
require __DIR__.'/purchasing.php';
require __DIR__.'/resep.php';
require __DIR__.'/stok-opname.php';
