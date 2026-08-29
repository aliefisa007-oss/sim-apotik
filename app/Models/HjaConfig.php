<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HjaConfig extends Model
{
    protected $table = 'hja_configs';

    protected $fillable = [
        'default_tax_percent',
        'harga_beli_termasuk_pajak_default',
        'default_metode',
        'default_markup_percent',
        'default_margin_percent',
        'rounding_method',
        'rounding_increment',
    ];

    protected $casts = [
        'default_tax_percent' => 'decimal:2',
        'harga_beli_termasuk_pajak_default' => 'boolean',
        'default_markup_percent' => 'decimal:2',
        'default_margin_percent' => 'decimal:2',
        'rounding_increment' => 'integer',
    ];

    /**
     * Ambil (atau buat jika belum ada) baris konfigurasi tunggal.
     * Dipakai sebagai default awal HJAService::calculate() — user tetap
     * bisa override per-kalkulasi lewat parameter eksplisit.
     */
    public static function current(): self
    {
        return self::query()->firstOrCreate([]);
    }
}
