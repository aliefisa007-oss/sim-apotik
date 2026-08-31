<?php

namespace App\Services;

use App\Models\BatchObat;
use App\Models\DetailStokOpname;
use App\Models\StokOpname;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * Orkestrasi sesi stok opname full-count. TIDAK menulis KartuStok/mengubah
 * BatchObat::stok_saat_ini secara langsung — saat sesi diselesaikan, setiap
 * item yang selisih di-delegasikan ke StockService::adjustStock() (satu-
 * satunya layer yang boleh mutasi stok riil, lihat §StockService).
 *
 * Alur: mulaiOpname() snapshot SEMUA batch aktif+habis (bukan expired) pada
 * momen itu -> catatHasilHitung() per item selama proses hitung fisik ->
 * selesaikanOpname() WAJIB semua item sudah dihitung, baru menyesuaikan stok
 * riil untuk item yang selisih != 0 dan mengunci sesi jadi 'selesai'.
 */
class StokOpnameService
{
    /**
     * Mulai sesi opname baru. Hanya boleh ada SATU sesi berstatus 'berjalan'
     * pada satu waktu (SENSIBLE DEFAULT — mencegah dua sesi opname tumpang
     * tindih membingungkan siapa yang bertanggung jawab atas selisih mana).
     */
    public function mulaiOpname(int $userId, ?string $catatan = null): StokOpname
    {
        if (StokOpname::where('status', StokOpname::STATUS_BERJALAN)->exists()) {
            throw new InvalidArgumentException('Masih ada sesi opname yang berjalan. Selesaikan atau batalkan dulu sebelum memulai sesi baru.');
        }

        return DB::transaction(function () use ($userId, $catatan) {
            $opname = StokOpname::create([
                'kode_opname' => $this->generateKodeOpname(),
                'tanggal_mulai' => now()->toDateString(),
                'status' => StokOpname::STATUS_BERJALAN,
                'dibuat_oleh' => $userId,
                'catatan' => $catatan,
            ]);

            // Full count: cakup semua batch yang masih diharapkan punya
            // stok tercatat (aktif ATAU habis — batch habis tetap perlu
            // diverifikasi fisik memang 0, bukan diasumsikan). Batch
            // expired dikecualikan karena sudah di-write-off terpisah
            // lewat ExpiryService, sama seperti filter di Penyesuaian Stok.
            $batchList = BatchObat::where('status', '!=', BatchObat::STATUS_EXPIRED)->get();

            foreach ($batchList as $batch) {
                DetailStokOpname::create([
                    'stok_opname_id' => $opname->id,
                    'batch_obat_id' => $batch->id,
                    'stok_sistem' => $batch->stok_saat_ini,
                    'harga_beli_saat_opname' => $batch->harga_beli,
                ]);
            }

            return $opname->fresh('detail');
        });
    }

    /**
     * Catat hasil hitung fisik untuk satu item. Belum menyentuh stok riil
     * sama sekali — murni pencatatan sampai sesi diselesaikan.
     */
    public function catatHasilHitung(int $detailId, int $stokFisik, int $userId, ?string $catatan = null): DetailStokOpname
    {
        if ($stokFisik < 0) {
            throw new InvalidArgumentException('Stok fisik tidak boleh negatif.');
        }

        $detail = DetailStokOpname::with('stokOpname')->findOrFail($detailId);

        if ($detail->stokOpname->status !== StokOpname::STATUS_BERJALAN) {
            throw new InvalidArgumentException('Sesi opname ini sudah tidak berjalan, tidak bisa mencatat hasil hitung baru.');
        }

        $detail->update([
            'stok_fisik' => $stokFisik,
            'catatan' => $catatan,
            'dihitung_oleh' => $userId,
            'dihitung_pada' => now(),
        ]);

        return $detail->fresh();
    }

    /**
     * Selesaikan sesi opname: WAJIB semua item sudah dihitung. Setiap item
     * dengan selisih != 0 disesuaikan lewat StockService::adjustStock()
     * (mengunci baris batch, menulis KartuStok jenis 'penyesuaian' dengan
     * keterangan merujuk ke kode_opname untuk traceability).
     */
    public function selesaikanOpname(int $opnameId, int $userId, StockService $stockService): StokOpname
    {
        return DB::transaction(function () use ($opnameId, $userId, $stockService) {
            $opname = StokOpname::with('detail')->lockForUpdate()->findOrFail($opnameId);

            if ($opname->status !== StokOpname::STATUS_BERJALAN) {
                throw new InvalidArgumentException("Sesi opname {$opname->kode_opname} sudah tidak berjalan.");
            }

            $belumDihitung = $opname->detail->whereNull('stok_fisik')->count();

            if ($belumDihitung > 0) {
                throw new RuntimeException("Masih ada {$belumDihitung} item yang belum dihitung. Semua item wajib dihitung sebelum sesi bisa diselesaikan.");
            }

            foreach ($opname->detail as $item) {
                if ($item->stok_fisik === $item->stok_sistem) {
                    continue;
                }

                $keterangan = "Opname {$opname->kode_opname}" . ($item->catatan ? " — {$item->catatan}" : '');
                $stockService->adjustStock($item->batch_obat_id, $item->stok_fisik, $userId, $keterangan);
            }

            $opname->update([
                'status' => StokOpname::STATUS_SELESAI,
                'tanggal_selesai' => now()->toDateString(),
                'diselesaikan_oleh' => $userId,
            ]);

            return $opname->fresh(['detail.batchObat.obat', 'pembuat', 'penyelesai']);
        });
    }

    /**
     * Batalkan sesi opname yang keliru dibuat/tidak jadi dilanjutkan.
     * Aman kapan saja selama status masih 'berjalan' — TIDAK ada dampak ke
     * stok riil karena penyesuaian riil baru terjadi di selesaikanOpname().
     */
    public function batalkanOpname(int $opnameId): StokOpname
    {
        $opname = StokOpname::findOrFail($opnameId);

        if ($opname->status !== StokOpname::STATUS_BERJALAN) {
            throw new InvalidArgumentException("Sesi opname {$opname->kode_opname} sudah tidak berjalan.");
        }

        $opname->update(['status' => StokOpname::STATUS_DIBATALKAN]);

        return $opname->fresh();
    }

    private function generateKodeOpname(): string
    {
        $bulan = now()->format('Ym');
        $prefix = "OPN-{$bulan}-";

        $last = StokOpname::where('kode_opname', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->orderByDesc('id')
            ->value('kode_opname');

        $nextNumber = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . sprintf('%04d', $nextNumber);
    }
}
