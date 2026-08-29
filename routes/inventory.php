<?php

use App\Livewire\Inventory\Batch\Index as BatchIndex;
use App\Livewire\Inventory\KartuStok\Index as KartuStokIndex;
use App\Livewire\Inventory\Penyesuaian\Form as PenyesuaianForm;
use App\Livewire\Inventory\StokMasuk\Form as StokMasukForm;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/batch', BatchIndex::class)->name('batch.index');
    Route::get('/stok-masuk', StokMasukForm::class)->name('stok-masuk.create');
    Route::get('/penyesuaian-stok', PenyesuaianForm::class)->name('penyesuaian-stok.create');
    Route::get('/kartu-stok', KartuStokIndex::class)->name('kartu-stok.index');
});
