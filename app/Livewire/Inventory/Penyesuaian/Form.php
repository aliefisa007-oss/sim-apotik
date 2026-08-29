<?php

namespace App\Livewire\Inventory\Penyesuaian;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Services\StockService;
use Livewire\Component;

class Form extends Component
{
    public ?int $obat_id = null;

    public ?int $batch_id = null;

    public ?int $stok_fisik_baru = null;

    public string $alasan = '';

    public function mount(): void
    {
        $this->authorize('create', BatchObat::class);
    }

    public function updatedObatId(): void
    {
        $this->reset(['batch_id', 'stok_fisik_baru']);
    }

    public function getBatchAktifProperty()
    {
        return $this->batch_id ? BatchObat::find($this->batch_id) : null;
    }

    public function getSelisihProperty(): ?int
    {
        if (!$this->batchAktif || $this->stok_fisik_baru === null) {
            return null;
        }

        return $this->stok_fisik_baru - $this->batchAktif->stok_saat_ini;
    }

    protected function rules(): array
    {
        return [
            'obat_id' => ['required', 'exists:obat,id'],
            'batch_id' => ['required', 'exists:batch_obat,id'],
            'stok_fisik_baru' => ['required', 'integer', 'min:0'],
            'alasan' => ['required', 'string', 'max:255'],
        ];
    }

    public function save(StockService $service): void
    {
        $this->validate();

        $batch = $service->adjustStock($this->batch_id, $this->stok_fisik_baru, auth()->id(), $this->alasan);

        session()->flash('success', "Stok batch {$batch->no_batch} disesuaikan menjadi {$batch->stok_saat_ini}.");

        $this->reset(['batch_id', 'stok_fisik_baru', 'alasan']);
    }

    public function render()
    {
        return view('livewire.inventory.penyesuaian.form', [
            'obatOptions' => Obat::where('is_active', true)->orderBy('nama_obat')->get(),
            'batchOptions' => $this->obat_id
                ? BatchObat::where('obat_id', $this->obat_id)->where('status', '!=', BatchObat::STATUS_EXPIRED)->orderBy('tanggal_kadaluarsa')->get()
                : collect(),
        ]);
    }
}
