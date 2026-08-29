<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\TransaksiPenjualan;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Query-only service untuk laporan penjualan. Semua angka mengecualikan
 * transaksi berstatus 'dibatalkan' (§PenjualanService::voidSale — void
 * tidak pernah delete, jadi harus di-filter eksplisit di sini supaya
 * laporan tidak menghitung transaksi yang sudah dibatalkan).
 */
class LaporanPenjualanService
{
    public function ringkasan(CarbonInterface $mulai, CarbonInterface $selesai): array
    {
        $query = TransaksiPenjualan::query()
            ->where('status', TransaksiPenjualan::STATUS_SELESAI)
            ->whereBetween('created_at', [$mulai->startOfDay(), $selesai->endOfDay()]);

        $totalTransaksi = (clone $query)->count();
        $totalOmzet = (float) (clone $query)->sum('total');

        return [
            'total_transaksi' => $totalTransaksi,
            'total_omzet' => $totalOmzet,
            'rata_rata_per_transaksi' => $totalTransaksi > 0 ? $totalOmzet / $totalTransaksi : 0.0,
        ];
    }

    /**
     * @return Collection<int, TransaksiPenjualan>
     */
    public function daftarTransaksi(CarbonInterface $mulai, CarbonInterface $selesai, ?int $kasirId = null): Collection
    {
        return TransaksiPenjualan::query()
            ->with(['kasir', 'resep'])
            ->where('status', TransaksiPenjualan::STATUS_SELESAI)
            ->whereBetween('created_at', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->when($kasirId, fn ($q) => $q->where('kasir_id', $kasirId))
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Omzet per hari dalam rentang — dipakai untuk grafik tren penjualan.
     *
     * @return Collection<int, array{tanggal: string, omzet: float, jumlah_transaksi: int}>
     */
    public function omzetPerHari(CarbonInterface $mulai, CarbonInterface $selesai): Collection
    {
        return TransaksiPenjualan::query()
            ->selectRaw('DATE(created_at) as tanggal, SUM(total) as omzet, COUNT(*) as jumlah_transaksi')
            ->where('status', TransaksiPenjualan::STATUS_SELESAI)
            ->whereBetween('created_at', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->map(fn ($row) => [
                'tanggal' => $row->tanggal,
                'omzet' => (float) $row->omzet,
                'jumlah_transaksi' => (int) $row->jumlah_transaksi,
            ]);
    }

    /**
     * Obat terlaris berdasarkan jumlah unit terjual dalam rentang tanggal.
     *
     * @return Collection<int, array{obat_id:int, nama_obat:string, jumlah_terjual:int, total_omzet:float}>
     */
    public function obatTerlaris(CarbonInterface $mulai, CarbonInterface $selesai, int $limit = 10): Collection
    {
        return DetailTransaksi::query()
            ->selectRaw('detail_transaksi.obat_id, obat.nama_obat, SUM(detail_transaksi.jumlah) as jumlah_terjual, SUM(detail_transaksi.subtotal) as total_omzet')
            ->join('obat', 'obat.id', '=', 'detail_transaksi.obat_id')
            ->join('transaksi_penjualan', 'transaksi_penjualan.id', '=', 'detail_transaksi.transaksi_id')
            ->where('transaksi_penjualan.status', TransaksiPenjualan::STATUS_SELESAI)
            ->whereBetween('transaksi_penjualan.created_at', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->groupBy('detail_transaksi.obat_id', 'obat.nama_obat')
            ->orderByDesc('jumlah_terjual')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'obat_id' => $row->obat_id,
                'nama_obat' => $row->nama_obat,
                'jumlah_terjual' => (int) $row->jumlah_terjual,
                'total_omzet' => (float) $row->total_omzet,
            ]);
    }
}
