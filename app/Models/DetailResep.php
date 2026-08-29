<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailResep extends Model
{
    protected $table = 'detail_resep';

    protected $fillable = [
        'resep_id',
        'obat_id',
        'jumlah_diresepkan',
        'jumlah_terlayani',
        'aturan_pakai',
        'catatan',
    ];

    protected $casts = [
        'jumlah_diresepkan' => 'integer',
        'jumlah_terlayani' => 'integer',
    ];

    public function resep(): BelongsTo
    {
        return $this->belongsTo(Resep::class, 'resep_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function sisaDiresepkan(): int
    {
        return max(0, $this->jumlah_diresepkan - $this->jumlah_terlayani);
    }
}
