<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPo extends Model
{
    protected $table = 'detail_po';

    protected $fillable = [
        'po_id',
        'obat_id',
        'jumlah_order',
        'jumlah_diterima',
        'harga_satuan',
    ];

    protected $casts = [
        'jumlah_order' => 'integer',
        'jumlah_diterima' => 'integer',
        'harga_satuan' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function sisaJumlah(): int
    {
        return max(0, $this->jumlah_order - $this->jumlah_diterima);
    }
}
