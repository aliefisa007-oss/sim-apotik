<?php

namespace App\Services;

use App\Models\DetailTransaksi;
use App\Models\TransaksiPenjualan;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Laporan keuangan sederhana: Omzet, HPP (Harga Pokok Penjualan), Laba
 * Kotor, dan Margin %.
 *
 * KETERBATASAN YANG DISADARI (TO BE VERIFIED / catatan untuk owner):
 * HPP dihitung dari detail_transaksi.jumlah × batch_obat.harga_beli SAAT
 * INI, bukan snapshot harga beli pada saat baris transaksi itu terjadi.
 * Karena keputusan Phase 2 (§StockService) meng-update harga_beli batch
 * ke harga pembelian TERAKHIR setiap kali batch yang sama menerima
 * restock, HPP historis pada laporan ini bisa sedikit bergeser kalau ada
 * batch yang di-restock dengan harga_beli berbeda SETELAH sebagian
 * stoknya sudah terjual. Untuk akurasi penuh, dibutuhkan kolom snapshot
 * harga_beli di detail_transaksi — di luar scope Phase 7, tidak saya
 * tambahkan tanpa konfirmasi karena berarti migration + perubahan
 * PenjualanService.
 */
class LaporanKeuanganService
{
    public function ringkasan(CarbonInterface $mulai, CarbonInterface $selesai): array
    {
        $omzet = (float) TransaksiPenjualan::query()
            ->where('status', TransaksiPenjualan::STATUS_SELESAI)
            ->whereBetween('created_at', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->sum('total');

        $hpp = (float) DetailTransaksi::query()
            ->join('transaksi_penjualan', 'transaksi_penjualan.id', '=', 'detail_transaksi.transaksi_id')
            ->join('batch_obat', 'batch_obat.id', '=', 'detail_transaksi.batch_id')
            ->where('transaksi_penjualan.status', TransaksiPenjualan::STATUS_SELESAI)
            ->whereBetween('transaksi_penjualan.created_at', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->selectRaw('SUM(detail_transaksi.jumlah * batch_obat.harga_beli) as hpp')
            ->value('hpp') ?? 0.0;

        $labaKotor = $omzet - $hpp;
        $marginPersen = $omzet > 0 ? ($labaKotor / $omzet) * 100 : 0.0;

        return [
            'omzet' => $omzet,
            'hpp' => $hpp,
            'laba_kotor' => $labaKotor,
            'margin_persen' => $marginPersen,
        ];
    }

    /**
     * @return Collection<int, array{tanggal:string, omzet:float, hpp:float, laba_kotor:float}>
     */
    public function labaRugiPerHari(CarbonInterface $mulai, CarbonInterface $selesai): Collection
    {
        $rows = DetailTransaksi::query()
            ->join('transaksi_penjualan', 'transaksi_penjualan.id', '=', 'detail_transaksi.transaksi_id')
            ->join('batch_obat', 'batch_obat.id', '=', 'detail_transaksi.batch_id')
            ->where('transaksi_penjualan.status', TransaksiPenjualan::STATUS_SELESAI)
            ->whereBetween('transaksi_penjualan.created_at', [$mulai->startOfDay(), $selesai->endOfDay()])
            ->selectRaw('DATE(transaksi_penjualan.created_at) as tanggal, SUM(detail_transaksi.subtotal) as omzet, SUM(detail_transaksi.jumlah * batch_obat.harga_beli) as hpp')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return $rows->map(fn ($row) => [
            'tanggal' => $row->tanggal,
            'omzet' => (float) $row->omzet,
            'hpp' => (float) $row->hpp,
            'laba_kotor' => (float) $row->omzet - (float) $row->hpp,
        ]);
    }
}
