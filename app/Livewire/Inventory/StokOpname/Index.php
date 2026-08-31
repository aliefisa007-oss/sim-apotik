<?php

namespace App\Livewire\Inventory\StokOpname;

use App\Models\StokOpname;
use App\Services\StokOpnameService;
use InvalidArgumentException;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', StokOpname::class);
    }

    public function mulai(StokOpnameService $service): void
    {
        $this->authorize('create', StokOpname::class);

        try {
            $opname = $service->mulaiOpname(auth()->id());
        } catch (InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->redirectRoute('stok-opname.show', $opname, navigate: true);
    }

    public function batalkan(int $id, StokOpnameService $service): void
    {
        $opname = StokOpname::findOrFail($id);
        $this->authorize('cancel', $opname);

        $service->batalkanOpname($id);

        session()->flash('success', "Sesi opname {$opname->kode_opname} dibatalkan.");
    }

    public function render()
    {
        return view('livewire.inventory.stok-opname.index', [
            'opnameList' => StokOpname::withCount('detail')
                ->with('pembuat')
                ->orderByDesc('id')
                ->paginate(15),
            'adaSesiBerjalan' => StokOpname::where('status', StokOpname::STATUS_BERJALAN)->exists(),
        ]);
    }
}
