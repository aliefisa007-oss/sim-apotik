<?php

namespace App\Services;

use App\Models\BatchObat;
use Illuminate\Support\Collection;

class ExpiryService
{
    public function __construct(private readonly StockService $stockService) {}

    public const THRESHOLDS = [90, 30, 7];

    /**
     * Batch aktif yang jatuh persis pada salah satu ambang notifikasi
     * (H-90 / H-30 / H-7), dikelompokkan per ambang. Query pakai index
     * tanggal_kadaluarsa + status (lihat migration) — aman dijalankan
     * scheduler harian tanpa full table scan.
     *
     * @return array<int, Collection<int, BatchObat>> key = jumlah hari (90|30|7)
     */
    public function batchesNearingExpiry(): array
    {
        $result = [];

        foreach (self::THRESHOLDS as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $result[$days] = BatchObat::query()
                ->where('status', BatchObat::STATUS_AKTIF)
                ->where('stok_saat_ini', '>', 0)
                ->whereDate('tanggal_kadaluarsa', $targetDate)
                ->with('obat')
                ->get();
        }

        return $result;
    }

    /**
     * Cari batch yang statusnya masih 'aktif' tapi tanggal_kadaluarsa sudah
     * lewat, lalu write-off semua sisa stoknya (via StockService, tercatat
     * di kartu_stok) dan set status -> expired.
     *
     * Dipanggil oleh command stok:cek-kadaluarsa. Batch yang sudah di-set
     * expired tidak akan lagi dipilih FEFOService (lihat scopeEligibleForFefo).
     *
     * @return int jumlah batch yang di-write-off
     */
    public function writeOffExpiredBatches(int $systemUserId): int
    {
        $expiredBatches = BatchObat::query()
            ->where('status', BatchObat::STATUS_AKTIF)
            ->whereDate('tanggal_kadaluarsa', '<', now()->toDateString())
            ->get(['id']);

        foreach ($expiredBatches as $batch) {
            $this->stockService->writeOffExpired($batch->id, $systemUserId);
        }

        return $expiredBatches->count();
    }
}
