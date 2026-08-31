<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailStokOpname extends Model
{
    use HasFactory;

    protected $table = 'detail_stok_opname';

    protected $fillable = [
        'stok_opname_id',
        'batch_obat_id',
        'stok_sistem',
        'stok_fisik',
        'harga_beli_saat_opname',
        'catatan',
        'dihitung_oleh',
        'dihitung_pada',
    ];

    protected $casts = [
        'stok_sistem' => 'integer',
        'stok_fisik' => 'integer',
        'harga_beli_saat_opname' => 'decimal:2',
        'dihitung_pada' => 'datetime',
    ];

    public function stokOpname(): BelongsTo
    {
        return $this->belongsTo(StokOpname::class);
    }

    public function batchObat(): BelongsTo
    {
        return $this->belongsTo(BatchObat::class);
    }

    public function penghitung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dihitung_oleh');
    }

    public function getSelisihAttribute(): ?int
    {
        if ($this->stok_fisik === null) {
            return null;
        }

        return $this->stok_fisik - $this->stok_sistem;
    }

    public function getNilaiSelisihAttribute(): ?string
    {
        if ($this->selisih === null) {
            return null;
        }

        return bcmul((string) $this->selisih, (string) $this->harga_beli_saat_opname, 2);
    }

    public function getSudahDihitungAttribute(): bool
    {
        return $this->stok_fisik !== null;
    }
}
