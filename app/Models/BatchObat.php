<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchObat extends Model
{
    use HasFactory;

    protected $table = 'batch_obat';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_HABIS = 'habis';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_DITARIK = 'ditarik';

    protected $fillable = [
        'obat_id',
        'supplier_id',
        'no_batch',
        'tanggal_produksi',
        'tanggal_kadaluarsa',
        'harga_beli',
        'harga_jual',
        'stok_awal',
        'stok_saat_ini',
        'status',
    ];

    protected $casts = [
        'tanggal_produksi' => 'date',
        'tanggal_kadaluarsa' => 'date',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok_awal' => 'integer',
        'stok_saat_ini' => 'integer',
    ];

    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function kartuStok(): HasMany
    {
        return $this->hasMany(KartuStok::class, 'batch_id');
    }

    /**
     * Batch yang boleh dipakai FEFOService: stok tersedia, status aktif,
     * dan belum lewat tanggal kadaluarsa. Diurutkan ASC oleh caller.
     */
    public function scopeEligibleForFefo(Builder $query, int $obatId): Builder
    {
        return $query
            ->where('obat_id', $obatId)
            ->where('status', self::STATUS_AKTIF)
            ->where('stok_saat_ini', '>', 0)
            ->where('tanggal_kadaluarsa', '>', now()->toDateString());
    }

    public function expiryStatus(): string
    {
        $daysLeft = now()->startOfDay()->diffInDays($this->tanggal_kadaluarsa, false);

        return match (true) {
            $daysLeft < 0 => 'expired',
            $daysLeft <= 7 => 'kritis',
            $daysLeft <= 30 => 'mendekati_expired',
            $daysLeft <= 90 => 'perhatian',
            default => 'aman',
        };
    }
}
