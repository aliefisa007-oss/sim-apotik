<?php

use App\Livewire\Hja\Config\Form as HjaConfigForm;
use App\Livewire\Hja\Kalkulator\Form as HjaKalkulatorForm;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/pengaturan/hja', HjaConfigForm::class)->name('hja-config.edit');
    Route::get('/batch/{batch}/hja', HjaKalkulatorForm::class)->name('hja-kalkulator.edit');
});
