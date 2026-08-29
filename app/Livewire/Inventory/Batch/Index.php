<?php

namespace App\Livewire\Inventory\Batch;

use App\Models\BatchObat;
use App\Models\Obat;
use Livewire\Component;

class Index extends Component
{
    public ?int $obat_id = null;

    public function mount(?int $obat_id = null): void
    {
        $this->obat_id = $obat_id;
    }

    public function render()
    {
        $batches = collect();

        if ($this->obat_id) {
            $batches = BatchObat::where('obat_id', $this->obat_id)
                ->with('supplier')
                ->orderBy('tanggal_kadaluarsa')
                ->get();
        }

        return view('livewire.inventory.batch.index', [
            'batches' => $batches,
            'obat' => $this->obat_id ? Obat::find($this->obat_id) : null,
            'obatOptions' => Obat::where('is_active', true)->orderBy('nama_obat')->get(),
        ]);
    }
}
