<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriHargaObat extends Model
{
    protected $table = 'histori_harga_obat';

    /**
     * Sama seperti KartuStok: ini audit trail, hanya ditulis lewat
     * HJAService::setHargaJual(), tidak pernah di-update/delete.
     */
    protected $fillable = [
        'obat_id',
        'batch_id',
        'harga_lama',
        'harga_baru',
        'harga_faktur',
        'diskon_persen',
        'harga_netto',
        'tax_percent',
        'harga_termasuk_pajak',
        'cost_basis',
        'metode_hja',
        'persen_markup_margin',
        'harga_sebelum_pembulatan',
        'rounding_method',
        'rounding_increment',
        'rounding_difference',
        'alasan',
        'user_id',
    ];

    protected $casts = [
        'harga_lama' => 'decimal:2',
        'harga_baru' => 'decimal:2',
        'harga_faktur' => 'decimal:2',
        'diskon_persen' => 'decimal:2',
        'harga_netto' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'harga_termasuk_pajak' => 'boolean',
        'cost_basis' => 'decimal:2',
        'persen_markup_margin' => 'decimal:2',
        'harga_sebelum_pembulatan' => 'decimal:2',
        'rounding_increment' => 'integer',
        'rounding_difference' => 'decimal:2',
    ];

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class, 'batch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
