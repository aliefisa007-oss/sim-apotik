<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPenerimaan extends Model
{
    protected $table = 'detail_penerimaan';

    protected $fillable = [
        'penerimaan_id',
        'obat_id',
        'no_batch',
        'tanggal_produksi',
        'tanggal_kadaluarsa',
        'harga_beli',
        'jumlah',
        'batch_id',
    ];

    protected $casts = [
        'tanggal_produksi' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'harga_beli' => 'decimal:2',
        'jumlah' => 'integer',
    ];

    public function penerimaan(): BelongsTo
    {
        return $this->belongsTo(PenerimaanBarang::class, 'penerimaan_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class, 'batch_id');
    }
}
