<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Obat extends Model
{
    use HasFactory;

    protected $table = 'obat';

    public const GOLONGAN_BEBAS = 'bebas';
    public const GOLONGAN_BEBAS_TERBATAS = 'bebas_terbatas';
    public const GOLONGAN_KERAS = 'keras';
    public const GOLONGAN_NARKOTIKA = 'narkotika';
    public const GOLONGAN_PSIKOTROPIKA = 'psikotropika';

    public const GOLONGAN_OPTIONS = [
        self::GOLONGAN_BEBAS,
        self::GOLONGAN_BEBAS_TERBATAS,
        self::GOLONGAN_KERAS,
        self::GOLONGAN_NARKOTIKA,
        self::GOLONGAN_PSIKOTROPIKA,
    ];

    protected $fillable = [
        'kode_obat',
        'nama_obat',
        'nama_generik',
        'kategori_id',
        'golongan',
        'satuan_dasar_id',
        'barcode',
        'butuh_resep',
        'deskripsi',
        'is_active',
        'stok_minimum',
    ];

    protected $casts = [
        'butuh_resep' => 'boolean',
        'is_active' => 'boolean',
        'stok_minimum' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriObat::class, 'kategori_id');
    }

    public function satuanDasar(): BelongsTo
    {
        return $this->belongsTo(Satuan::class, 'satuan_dasar_id');
    }

    public function obatSatuan(): HasMany
    {
        return $this->hasMany(ObatSatuan::class);
    }

    public function batchObat(): HasMany
    {
        return $this->hasMany(BatchObat::class, 'obat_id');
    }

    /**
     * Golongan that requires apoteker approval at point of sale.
     * Rule lives here (single source of truth) so Blade/Livewire/API
     * never re-implement it — per master prompt §54/§19.
     * NOTE: enforcement itself is a Phase 6 (ApprovalService) concern;
     * this is only the classification check.
     */
    public function requiresApprovalGolongan(): bool
    {
        return in_array($this->golongan, [
            self::GOLONGAN_KERAS,
            self::GOLONGAN_NARKOTIKA,
            self::GOLONGAN_PSIKOTROPIKA,
        ], true);
    }
}
