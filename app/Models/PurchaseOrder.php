<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $table = 'purchase_order';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_DIKIRIM = 'dikirim';
    public const STATUS_DITERIMA_SEBAGIAN = 'diterima_sebagian';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_BATAL = 'batal';

    protected $fillable = [
        'no_po',
        'supplier_id',
        'tanggal_po',
        'status',
        'total',
        'user_id',
    ];

    protected $casts = [
        'tanggal_po' => 'date',
        'total' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailPo::class, 'po_id');
    }

    public function penerimaan(): HasMany
    {
        return $this->hasMany(PenerimaanBarang::class, 'po_id');
    }
}
