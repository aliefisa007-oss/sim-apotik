<?php

namespace App\Livewire\Laporan;

use App\Policies\LaporanPolicy;
use App\Services\LaporanPenjualanService;
use Carbon\Carbon;
use Livewire\Component;

class Penjualan extends Component
{
    public string $tanggalMulai = '';
    public string $tanggalSelesai = '';

    public function mount(): void
    {
        abort_unless(app(LaporanPolicy::class)->view(auth()->user()), 403);

        $this->tanggalMulai = now()->startOfMonth()->toDateString();
        $this->tanggalSelesai = now()->toDateString();
    }

    public function render(LaporanPenjualanService $service)
    {
        $mulai = Carbon::parse($this->tanggalMulai);
        $selesai = Carbon::parse($this->tanggalSelesai);

        return view('livewire.laporan.penjualan', [
            'ringkasan' => $service->ringkasan($mulai, $selesai),
            'omzetPerHari' => $service->omzetPerHari($mulai, $selesai),
            'obatTerlaris' => $service->obatTerlaris($mulai, $selesai),
            'daftarTransaksi' => $service->daftarTransaksi($mulai, $selesai),
        ]);
    }
}
