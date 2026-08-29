<?php

use App\Livewire\MasterData\User\Index as UserIndex;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/pengguna', UserIndex::class)->name('pengguna.index');
});
