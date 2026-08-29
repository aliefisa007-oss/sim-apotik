<?php

namespace App\Livewire\Laporan;

use App\Policies\LaporanPolicy;
use App\Services\LaporanStokService;
use Livewire\Component;

class Stok extends Component
{
    public bool $hanyaMenipis = false;
    public int $dalamHari = 90;

    public function mount(): void
    {
        abort_unless(app(LaporanPolicy::class)->view(auth()->user()), 403);
    }

    public function render(LaporanStokService $service)
    {
        return view('livewire.laporan.stok', [
            'stokSaatIni' => $service->stokSaatIni($this->hanyaMenipis),
            'batchMendekatiKadaluarsa' => $service->batchMendekatiKadaluarsa($this->dalamHari),
            'nilaiPersediaan' => $service->nilaiPersediaan(),
        ]);
    }
}
