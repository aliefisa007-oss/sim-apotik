<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\BatchObat;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FEFOService
{
    public function __construct(private readonly StockService $stockService) {}

    /**
     * Hitung alokasi FEFO tanpa memutasi stok — dipakai untuk preview
     * (misal menampilkan "akan diambil dari batch A: 5, batch B: 3" di UI
     * sebelum transaksi dikonfirmasi).
     *
     * @return array<int, array{batch_id: int, no_batch: string, tanggal_kadaluarsa: string, jumlah: int}>
     * @throws InsufficientStockException jika total stok aktif < $jumlahDiminta
     */
    public function previewAllocation(int $obatId, int $jumlahDiminta): array
    {
        if ($jumlahDiminta <= 0) {
            throw new InvalidArgumentException('Jumlah yang diminta harus lebih dari 0.');
        }

        $batches = BatchObat::eligibleForFefo($obatId)
            ->orderBy('tanggal_kadaluarsa', 'asc')
            ->get();

        $sisa = $jumlahDiminta;
        $allocations = [];

        foreach ($batches as $batch) {
            if ($sisa <= 0) {
                break;
            }

            $ambil = min($sisa, $batch->stok_saat_ini);

            $allocations[] = [
                'batch_id' => $batch->id,
                'no_batch' => $batch->no_batch,
                'tanggal_kadaluarsa' => $batch->tanggal_kadaluarsa->toDateString(),
                'jumlah' => $ambil,
            ];

            $sisa -= $ambil;
        }

        if ($sisa > 0) {
            $tersedia = $jumlahDiminta - $sisa;
            throw InsufficientStockException::forObat($obatId, $jumlahDiminta, $tersedia);
        }

        return $allocations;
    }

    /**
     * Alokasikan DAN kurangi stok sekaligus, atomic. Mengunci batch-batch
     * yang terlibat secara berurutan (ASC tanggal_kadaluarsa) di dalam satu
     * transaction supaya dua penjualan obat yang sama secara bersamaan tidak
     * saling menimpa (§8) — locking terjadi di StockService::deductFromBatch.
     *
     * @return array<int, array{batch_id: int, no_batch: string, jumlah: int}>
     */
    public function deduct(
        int $obatId,
        int $jumlahDiminta,
        string $jenisTransaksi,
        int $userId,
        ?string $referensiType = null,
        ?int $referensiId = null,
    ): array {
        return DB::transaction(function () use ($obatId, $jumlahDiminta, $jenisTransaksi, $userId, $referensiType, $referensiId) {
            // Preview di dalam transaction yang sama supaya alokasi dihitung
            // dari state ter-lock, bukan snapshot lama.
            $allocations = $this->previewAllocation($obatId, $jumlahDiminta);

            $result = [];

            foreach ($allocations as $allocation) {
                $this->stockService->deductFromBatch(
                    batchId: $allocation['batch_id'],
                    jumlah: $allocation['jumlah'],
                    jenisTransaksi: $jenisTransaksi,
                    userId: $userId,
                    referensiType: $referensiType,
                    referensiId: $referensiId,
                );

                $result[] = $allocation;
            }

            return $result;
        });
    }
}
