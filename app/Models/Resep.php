<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resep extends Model
{
    use HasFactory;

    protected $table = 'resep';

    public const STATUS_MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const STATUS_TERVERIFIKASI = 'terverifikasi';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_SELESAI = 'selesai';

    protected $fillable = [
        'no_resep',
        'pasien_id',
        'nama_dokter',
        'no_sip_dokter',
        'tanggal_resep',
        'status',
        'apoteker_verifikasi_id',
        'catatan_verifikasi',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'tanggal_resep' => 'date',
        'verified_at' => 'datetime',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function apotekerVerifikasi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'apoteker_verifikasi_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function detail(): HasMany
    {
        return $this->hasMany(DetailResep::class, 'resep_id');
    }

    /**
     * Transaksi penjualan yang sudah dispensing dari resep ini. Nullable
     * pada transaksi_penjualan tetap 1:many secara skema (FK biasa), tapi
     * relasi ini dipakai sebagai "transaksi terakhir/utama" di UI riwayat
     * resep — lihat catatan di ResepService soal dispensing sebagian.
     */
    public function transaksi(): HasMany
    {
        return $this->hasMany(TransaksiPenjualan::class, 'resep_id');
    }

    public function requiresApoteker(): bool
    {
        return $this->detail->contains(fn (DetailResep $d) => $d->obat->requiresApprovalGolongan());
    }

    public function isFullyDispensed(): bool
    {
        return $this->detail->every(fn (DetailResep $d) => $d->jumlah_terlayani >= $d->jumlah_diresepkan);
    }
}
