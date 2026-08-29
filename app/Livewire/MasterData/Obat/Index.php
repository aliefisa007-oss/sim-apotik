<?php

namespace App\Livewire\MasterData\Obat;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Services\ObatService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public string $golonganFilter = '';

    public string $kategoriFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingGolonganFilter(): void
    {
        $this->resetPage();
    }

    public function updatingKategoriFilter(): void
    {
        $this->resetPage();
    }

    public function deactivate(int $obatId, ObatService $service): void
    {
        $obat = Obat::findOrFail($obatId);
        $this->authorize('deactivate', $obat);

        $service->deactivate($obat);

        session()->flash('success', "Obat {$obat->kode_obat} dinonaktifkan.");
    }

    public function render()
    {
        $obat = Obat::query()
            ->with(['kategori', 'satuanDasar'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nama_obat', 'like', "%{$this->search}%")
                        ->orWhere('nama_generik', 'like', "%{$this->search}%")
                        ->orWhere('barcode', $this->search)
                        ->orWhere('kode_obat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->golonganFilter, fn ($query) => $query->where('golongan', $this->golonganFilter))
            ->when($this->kategoriFilter, fn ($query) => $query->where('kategori_id', $this->kategoriFilter))
            ->orderBy('nama_obat')
            ->paginate(20);

        return view('livewire.master-data.obat.index', [
            'obatList' => $obat,
            'kategoriOptions' => KategoriObat::where('is_active', true)->orderBy('nama')->get(),
            'golonganOptions' => Obat::GOLONGAN_OPTIONS,
        ]);
    }
}
