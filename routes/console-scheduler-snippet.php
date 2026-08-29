<?php

// Tambahkan baris ini ke routes/console.php (Laravel 11/12) atau
// app/Console/Kernel.php::schedule() (Laravel <=10):

use Illuminate\Support\Facades\Schedule;

Schedule::command('stok:cek-kadaluarsa')->dailyAt('06:00');
