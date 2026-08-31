<?php

use App\Livewire\Inventory\StokOpname\Index as StokOpnameIndex;
use App\Livewire\Inventory\StokOpname\Show as StokOpnameShow;
use App\Models\StokOpname;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/stok-opname', StokOpnameIndex::class)->name('stok-opname.index');
    Route::get('/stok-opname/{opname}', StokOpnameShow::class)->name('stok-opname.show');

    Route::get('/stok-opname/{opname}/cetak', function (StokOpname $opname) {
        abort_unless(Auth::user()->can('view', $opname), 403);

        return view('stok-opname.cetak', [
            'opname' => $opname->load(['detail.batchObat.obat', 'pembuat', 'penyelesai']),
        ]);
    })->name('stok-opname.cetak');
});
