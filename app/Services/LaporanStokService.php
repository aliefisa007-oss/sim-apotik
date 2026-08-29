<?php

namespace App\Services;

use App\Models\BatchObat;
use App\Models\Obat;
use Illuminate\Support\Collection;

/**
 * Query-only service untuk laporan stok. Berbeda dari ExpiryService
 * (yang dipakai command scheduler untuk notifikasi tepat di H-90/H-30/H-7
 * dan write-off), service ini untuk TAMPILAN LAPORAN — query rentang
 * ("kadaluarsa dalam N hari ke depan"), bukan titik ambang tunggal.
 */
class LaporanStokService
{
    /**
     * Stok saat ini per obat (dijumlah dari semua batch aktif), plus flag
     * menipis berdasarkan Obat::stok_minimum.
     *
     * @return Collection<int, array{obat_id:int, kode_obat:string, nama_obat:string, stok_total:int, stok_minimum:int, menipis:bool}>
     */
    public function stokSaatIni(bool $hanyaMenipis = false): Collection
    {
        $rows = Obat::query()
            ->where('is_active', true)
            ->withSum(['batchObat as stok_total' => fn ($q) => $q->where('status', BatchObat::STATUS_AKTIF)], 'stok_saat_ini')
            ->orderBy('nama_obat')
            ->get()
            ->map(fn (Obat $obat) => [
                'obat_id' => $obat->id,
                'kode_obat' => $obat->kode_obat,
                'nama_obat' => $obat->nama_obat,
                'stok_total' => (int) ($obat->stok_total ?? 0),
                'stok_minimum' => $obat->stok_minimum,
                'menipis' => (int) ($obat->stok_total ?? 0) <= $obat->stok_minimum,
            ]);

        return $hanyaMenipis ? $rows->filter(fn ($r) => $r['menipis'])->values() : $rows;
    }

    /**
     * Batch aktif dengan stok > 0 yang kadaluarsa dalam N hari ke depan
     * (rentang, bukan titik ambang tunggal seperti ExpiryService).
     *
     * @return Collection<int, BatchObat>
     */
    public function batchMendekatiKadaluarsa(int $dalamHari = 90): Collection
    {
        return BatchObat::query()
            ->with('obat')
            ->where('status', BatchObat::STATUS_AKTIF)
            ->where('stok_saat_ini', '>', 0)
            ->whereDate('tanggal_kadaluarsa', '<=', now()->addDays($dalamHari)->toDateString())
            ->orderBy('tanggal_kadaluarsa')
            ->get();
    }

    public function nilaiPersediaan(): float
    {
        return (float) BatchObat::query()
            ->where('status', BatchObat::STATUS_AKTIF)
            ->selectRaw('SUM(stok_saat_ini * harga_beli) as nilai')
            ->value('nilai') ?? 0.0;
    }
}
