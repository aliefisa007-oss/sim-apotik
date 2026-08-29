<?php

namespace App\Livewire\Purchasing\PurchaseOrder;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function kirim(int $poId, PurchaseOrderService $service): void
    {
        $po = PurchaseOrder::findOrFail($poId);
        $this->authorize('update', $po);

        try {
            $service->kirim($po);
            session()->flash('success', "PO {$po->no_po} dikirim ke supplier.");
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function batalkan(int $poId, PurchaseOrderService $service): void
    {
        $po = PurchaseOrder::findOrFail($poId);
        $this->authorize('update', $po);

        try {
            $service->batalkan($po);
            session()->flash('success', "PO {$po->no_po} dibatalkan.");
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchasing.purchase-order.index', [
            'poList' => PurchaseOrder::query()
                ->with('supplier')
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->latest()
                ->paginate(20),
        ]);
    }
}
