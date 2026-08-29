<?php

use App\Livewire\Penjualan\Kasir\Form as KasirForm;
use App\Livewire\Penjualan\Riwayat\Index as RiwayatIndex;
use App\Models\TransaksiPenjualan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/kasir', KasirForm::class)->name('penjualan.kasir');
    Route::get('/riwayat-transaksi', RiwayatIndex::class)->name('penjualan.riwayat');

    Route::get('/struk/{transaksi}', function (TransaksiPenjualan $transaksi) {
        abort_unless(Auth::user()->can('view', $transaksi), 403);

        return view('penjualan.struk', [
            'transaksi' => $transaksi->load(['detail.obat', 'kasir', 'apotekerApproval']),
        ]);
    })->name('penjualan.struk');
});
