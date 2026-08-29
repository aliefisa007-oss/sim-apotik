<?php

namespace App\Livewire\Purchasing\PenerimaanBarang;

use App\Models\PenerimaanBarang;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.purchasing.penerimaan-barang.index', [
            'penerimaanList' => PenerimaanBarang::query()
                ->with(['purchaseOrder', 'user', 'detail'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
