<?php

use App\Livewire\MasterData\Pasien\Index as PasienIndex;
use App\Livewire\Resep\Form as ResepForm;
use App\Livewire\Resep\Index as ResepIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/pasien', PasienIndex::class)->name('pasien.index');

    Route::prefix('resep')->name('resep.')->group(function () {
        Route::get('/', ResepIndex::class)->name('index');
        Route::get('/tambah', ResepForm::class)->name('create');
    });
});
