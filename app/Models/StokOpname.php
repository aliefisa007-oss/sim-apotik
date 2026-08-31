<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StokOpname extends Model
{
    use HasFactory;

    protected $table = 'stok_opname';

    public const STATUS_BERJALAN = 'berjalan';
    public const STATUS_SELESAI = 'selesai';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    protected $fillable = [
        'kode_opname',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'dibuat_oleh',
        'diselesaikan_oleh',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function detail(): HasMany
    {
        return $this->hasMany(DetailStokOpname::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penyelesai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diselesaikan_oleh');
    }

    public function getJumlahItemAttribute(): int
    {
        return $this->detail->count();
    }

    public function getJumlahSudahDihitungAttribute(): int
    {
        return $this->detail->whereNotNull('stok_fisik')->count();
    }

    public function getSudahLengkapAttribute(): bool
    {
        return $this->jumlah_item > 0 && $this->jumlah_sudah_dihitung === $this->jumlah_item;
    }
}
