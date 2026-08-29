<?php

namespace App\Services;

use App\Models\Resep;
use App\Models\TransaksiPenjualan;

/**
 * Mengumpulkan ringkasan dari service laporan lain untuk satu layar
 * dashboard — TIDAK menduplikasi query logic (§77), hanya orkestrasi
 * pemanggilan LaporanPenjualanService/LaporanStokService/
 * LaporanKeuanganService dengan rentang "hari ini" & "bulan ini".
 */
class DashboardService
{
    public function __construct(
        private readonly LaporanPenjualanService $laporanPenjualan,
        private readonly LaporanStokService $laporanStok,
        private readonly LaporanKeuanganService $laporanKeuangan,
    ) {}

    public function ringkasan(): array
    {
        $hariIni = now();
        $awalBulan = now()->startOfMonth();

        return [
            'penjualan_hari_ini' => $this->laporanPenjualan->ringkasan($hariIni, $hariIni),
            'penjualan_bulan_ini' => $this->laporanPenjualan->ringkasan($awalBulan, $hariIni),
            'keuangan_bulan_ini' => $this->laporanKeuangan->ringkasan($awalBulan, $hariIni),
            'stok_menipis' => $this->laporanStok->stokSaatIni(hanyaMenipis: true)->count(),
            'batch_mendekati_kadaluarsa' => $this->laporanStok->batchMendekatiKadaluarsa(30)->count(),
            'resep_menunggu_verifikasi' => Resep::where('status', Resep::STATUS_MENUNGGU_VERIFIKASI)->count(),
            'transaksi_terbaru' => TransaksiPenjualan::query()
                ->with('kasir')
                ->where('status', TransaksiPenjualan::STATUS_SELESAI)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
        ];
    }
}
