<?php

namespace App\Livewire\Inventory\StokOpname;

use App\Models\DetailStokOpname;
use App\Models\StokOpname;
use App\Services\StockService;
use App\Services\StokOpnameService;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

class Show extends Component
{
    use WithPagination;

    public StokOpname $opname;

    public string $search = '';

    /**
     * Input stok fisik per baris, di-key oleh detail_id — supaya semua
     * baris di halaman saat ini bisa diisi lalu disimpan satu-satu lewat
     * wire:change tanpa reload seluruh tabel per input.
     */
    public array $stokFisikInput = [];

    public array $catatanInput = [];

    public function mount(StokOpname $opname): void
    {
        $this->authorize('view', $opname);
        $this->opname = $opname;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function simpanHitung(int $detailId, StokOpnameService $service): void
    {
        $this->authorize('create', StokOpname::class);

        $stokFisik = $this->stokFisikInput[$detailId] ?? null;

        if ($stokFisik === null || $stokFisik === '') {
            return;
        }

        try {
            $service->catatHasilHitung(
                $detailId,
                (int) $stokFisik,
                auth()->id(),
                $this->catatanInput[$detailId] ?? null
            );
        } catch (InvalidArgumentException $e) {
            $this->addError("stokFisikInput.{$detailId}", $e->getMessage());

            return;
        }

        $this->opname->refresh();
    }

    public function selesaikan(StokOpnameService $service, StockService $stockService): void
    {
        $this->authorize('finalize', $this->opname);

        try {
            $this->opname = $service->selesaikanOpname($this->opname->id, auth()->id(), $stockService);
        } catch (RuntimeException|InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        session()->flash('success', "Opname {$this->opname->kode_opname} selesai. Stok riil sudah disesuaikan untuk item yang selisih.");
    }

    public function batalkan(StokOpnameService $service): void
    {
        $this->authorize('cancel', $this->opname);

        $this->opname = $service->batalkanOpname($this->opname->id);

        session()->flash('success', "Sesi opname {$this->opname->kode_opname} dibatalkan.");

        $this->redirectRoute('stok-opname.index', navigate: true);
    }

    public function render()
    {
        $detailQuery = DetailStokOpname::where('stok_opname_id', $this->opname->id)
            ->with(['batchObat.obat', 'penghitung']);

        if ($this->search) {
            $detailQuery->whereHas('batchObat.obat', fn ($q) => $q->where('nama_obat', 'like', "%{$this->search}%")
                ->orWhere('kode_obat', 'like', "%{$this->search}%"))
                ->orWhereHas('batchObat', fn ($q) => $q->where('no_batch', 'like', "%{$this->search}%"));
        }

        $detailList = $detailQuery->orderBy('id')->paginate(25);

        foreach ($detailList as $detail) {
            if (!array_key_exists($detail->id, $this->stokFisikInput)) {
                $this->stokFisikInput[$detail->id] = $detail->stok_fisik;
                $this->catatanInput[$detail->id] = $detail->catatan;
            }
        }

        // Ringkasan dihitung dari SELURUH item sesi (bukan hanya halaman
        // saat ini) — query terpisah tanpa pagination.
        $semuaDetail = DetailStokOpname::where('stok_opname_id', $this->opname->id)->get();

        return view('livewire.inventory.stok-opname.show', [
            'detailList' => $detailList,
            'ringkasan' => [
                'total' => $semuaDetail->count(),
                'sudah_dihitung' => $semuaDetail->whereNotNull('stok_fisik')->count(),
                'sesuai' => $semuaDetail->filter(fn ($d) => $d->stok_fisik !== null && $d->selisih === 0)->count(),
                'lebih' => $semuaDetail->filter(fn ($d) => $d->stok_fisik !== null && $d->selisih > 0)->count(),
                'kurang' => $semuaDetail->filter(fn ($d) => $d->stok_fisik !== null && $d->selisih < 0)->count(),
                'nilai_selisih' => $semuaDetail->filter(fn ($d) => $d->stok_fisik !== null)->sum(fn ($d) => (float) $d->nilai_selisih),
            ],
        ]);
    }
}
