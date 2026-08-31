<?php

namespace App\Livewire\Inventory\Batch;

use App\Models\BatchObat;
use App\Models\Obat;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'obat_id')]
public string|int|null $obat_id = null;

    public function render()
    {
        $obatId = $this->obat_id !== null && $this->obat_id !== ''
            ? (int) $this->obat_id
            : null;

        $batches = collect();

        if ($obatId) {
            $batches = BatchObat::where('obat_id', $obatId)
                ->with('supplier')
                ->orderBy('tanggal_kadaluarsa')
                ->get();
        }

        return view('livewire.inventory.batch.index', [
            'batches' => $batches,
            'obat' => $obatId ? Obat::find($obatId) : null,
            'obatOptions' => Obat::where('is_active', true)->orderBy('nama_obat')->get(),
        ]);
    }
}