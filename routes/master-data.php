<?php

use App\Livewire\MasterData\KategoriObat\Index as KategoriObatIndex;
use App\Livewire\MasterData\Obat\Form as ObatForm;
use App\Livewire\MasterData\Obat\Index as ObatIndex;
use App\Livewire\MasterData\Satuan\Index as SatuanIndex;
use App\Livewire\MasterData\Supplier\Index as SupplierIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('obat')->name('obat.')->group(function () {
        Route::get('/', ObatIndex::class)->name('index');
        Route::get('/tambah', ObatForm::class)->name('create');
        Route::get('/{obat}/edit', ObatForm::class)->name('edit');
    });

    Route::get('/kategori-obat', KategoriObatIndex::class)->name('kategori-obat.index');
    Route::get('/satuan', SatuanIndex::class)->name('satuan.index');
    Route::get('/supplier', SupplierIndex::class)->name('supplier.index');
});
