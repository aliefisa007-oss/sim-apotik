<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KartuStok extends Model
{
    use HasFactory;

    protected $table = 'kartu_stok';

    public const JENIS_MASUK_PEMBELIAN = 'masuk_pembelian';
    public const JENIS_KELUAR_JUAL = 'keluar_jual';
    public const JENIS_PENYESUAIAN = 'penyesuaian';
    public const JENIS_EXPIRED_WRITEOFF = 'expired_writeoff';
    public const JENIS_RETUR = 'retur';

    /**
     * Kartu stok adalah audit trail — tidak ada update()/delete() yang
     * diekspos di luar migration rollback. Setiap baris dibuat sekali oleh
     * StockService dan tidak pernah diubah.
     */
    protected $fillable = [
        'obat_id',
        'batch_id',
        'jenis_transaksi',
        'jumlah',
        'saldo_sebelum',
        'saldo_sesudah',
        'referensi_id',
        'referensi_type',
        'user_id',
        'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'saldo_sebelum' => 'integer',
        'saldo_sesudah' => 'integer',
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
