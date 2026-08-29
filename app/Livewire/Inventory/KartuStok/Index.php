<?php

namespace App\Livewire\Inventory\KartuStok;

use App\Models\KartuStok;
use App\Models\Obat;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $obat_id = null;

    public string $jenisFilter = '';

    public string $dari = '';

    public string $sampai = '';

    public function mount(?int $obat_id = null): void
    {
        $this->obat_id = $obat_id;
    }

    public function updatingJenisFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDari(): void
    {
        $this->resetPage();
    }

    public function updatingSampai(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $riwayat = KartuStok::query()
            ->with(['obat', 'batch', 'user'])
            ->when($this->obat_id, fn ($q) => $q->where('obat_id', $this->obat_id))
            ->when($this->jenisFilter, fn ($q) => $q->where('jenis_transaksi', $this->jenisFilter))
            // !empty(), bukan isset() — string kosong dari input date yang
            // dikosongkan tetap harus dianggap "tidak difilter" (pelajaran
            // dari bug filter tanggal riwayat stok SIM Coffee).
            ->when(!empty($this->dari), fn ($q) => $q->whereDate('created_at', '>=', $this->dari))
            ->when(!empty($this->sampai), fn ($q) => $q->whereDate('created_at', '<=', $this->sampai))
            ->latest()
            ->paginate(25);

        return view('livewire.inventory.kartu-stok.index', [
            'riwayat' => $riwayat,
            'obat' => $this->obat_id ? Obat::find($this->obat_id) : null,
            'obatOptions' => Obat::orderBy('nama_obat')->get(),
            'jenisOptions' => [
                KartuStok::JENIS_MASUK_PEMBELIAN => 'Masuk (Pembelian)',
                KartuStok::JENIS_KELUAR_JUAL => 'Keluar (Jual)',
                KartuStok::JENIS_PENYESUAIAN => 'Penyesuaian',
                KartuStok::JENIS_EXPIRED_WRITEOFF => 'Write-off Expired',
                KartuStok::JENIS_RETUR => 'Retur',
            ],
        ]);
    }
}
