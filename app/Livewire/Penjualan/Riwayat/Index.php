<?php

namespace App\Livewire\Penjualan\Riwayat;

use App\Models\TransaksiPenjualan;
use App\Services\PenjualanService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $dari = '';
    public string $sampai = '';
    public string $statusFilter = '';

    public bool $showVoidModal = false;
    public ?int $voidingId = null;
    public string $alasanVoid = '';

    public function openVoid(int $transaksiId): void
    {
        $transaksi = TransaksiPenjualan::findOrFail($transaksiId);
        $this->authorize('void', $transaksi);

        $this->voidingId = $transaksiId;
        $this->alasanVoid = '';
        $this->showVoidModal = true;
    }

    public function confirmVoid(PenjualanService $service): void
    {
        $this->validate(['alasanVoid' => ['required', 'string', 'max:255']]);

        $transaksi = TransaksiPenjualan::findOrFail($this->voidingId);
        $this->authorize('void', $transaksi);

        $service->voidSale($transaksi, auth()->id(), $this->alasanVoid);

        session()->flash('success', "Transaksi {$transaksi->no_transaksi} dibatalkan, stok dikembalikan.");
        $this->showVoidModal = false;
    }

    public function render()
    {
        $riwayat = TransaksiPenjualan::query()
            ->with(['kasir', 'detail'])
            ->when(!auth()->user()->hasRole('owner') && !auth()->user()->hasRole('admin'), fn ($q) => $q->where('kasir_id', auth()->id()))
            ->when(!empty($this->dari), fn ($q) => $q->whereDate('created_at', '>=', $this->dari))
            ->when(!empty($this->sampai), fn ($q) => $q->whereDate('created_at', '<=', $this->sampai))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(20);

        return view('livewire.penjualan.riwayat.index', ['riwayat' => $riwayat]);
    }
}
