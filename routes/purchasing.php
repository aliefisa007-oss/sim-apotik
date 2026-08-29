<?php

use App\Livewire\Purchasing\PenerimaanBarang\Form as PenerimaanBarangForm;
use App\Livewire\Purchasing\PenerimaanBarang\Index as PenerimaanBarangIndex;
use App\Livewire\Purchasing\PurchaseOrder\Form as PurchaseOrderForm;
use App\Livewire\Purchasing\PurchaseOrder\Index as PurchaseOrderIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::prefix('purchase-order')->name('purchase-order.')->group(function () {
        Route::get('/', PurchaseOrderIndex::class)->name('index');
        Route::get('/tambah', PurchaseOrderForm::class)->name('create');
    });

    Route::prefix('penerimaan-barang')->name('penerimaan-barang.')->group(function () {
        Route::get('/', PenerimaanBarangIndex::class)->name('index');
        Route::get('/tambah', PenerimaanBarangForm::class)->name('create');
    });
});
