<?php

namespace App\Livewire\Laporan;

use App\Policies\LaporanPolicy;
use App\Services\LaporanKeuanganService;
use Carbon\Carbon;
use Livewire\Component;

class Keuangan extends Component
{
    public string $tanggalMulai = '';
    public string $tanggalSelesai = '';

    public function mount(): void
    {
        // Laporan keuangan lebih sensitif dari laporan lain — dibatasi
        // owner SAJA (bukan admin), berbeda dari LaporanPolicy default.
        // TO BE VERIFIED apakah admin memang tidak boleh lihat keuangan.
        abort_unless(auth()->user()->hasRole('owner'), 403);

        $this->tanggalMulai = now()->startOfMonth()->toDateString();
        $this->tanggalSelesai = now()->toDateString();
    }

    public function render(LaporanKeuanganService $service)
    {
        $mulai = Carbon::parse($this->tanggalMulai);
        $selesai = Carbon::parse($this->tanggalSelesai);

        return view('livewire.laporan.keuangan', [
            'ringkasan' => $service->ringkasan($mulai, $selesai),
            'labaRugiPerHari' => $service->labaRugiPerHari($mulai, $selesai),
        ]);
    }
}
