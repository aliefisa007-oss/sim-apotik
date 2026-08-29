<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ObatSatuan extends Model
{
    use HasFactory;

    protected $table = 'obat_satuan';

    protected $fillable = [
        'obat_id',
        'satuan_id',
        'konversi_ke_satuan_dasar',
        'is_satuan_dasar',
        'is_satuan_jual_default',
    ];

    protected $casts = [
        'konversi_ke_satuan_dasar' => 'integer',
        'is_satuan_dasar' => 'boolean',
        'is_satuan_jual_default' => 'boolean',
    ];

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function satuan(): BelongsTo
    {
        return $this->belongsTo(Satuan::class);
    }
}
