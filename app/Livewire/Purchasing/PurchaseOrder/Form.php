<?php

namespace App\Livewire\Purchasing\PurchaseOrder;

use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseOrderService;
use Livewire\Component;

class Form extends Component
{
    public ?int $supplier_id = null;
    public string $tanggal_po = '';

    /** @var array<int, array{obat_id: ?int, jumlah_order: ?int, harga_satuan: ?float}> */
    public array $items = [];

    public function mount(): void
    {
        $this->authorize('create', PurchaseOrder::class);
        $this->tanggal_po = now()->toDateString();
        $this->addItem();
    }

    public function addItem(): void
    {
        $this->items[] = ['obat_id' => null, 'jumlah_order' => null, 'harga_satuan' => null];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function rules(): array
    {
        return [
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'tanggal_po' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.jumlah_order' => ['required', 'integer', 'min:1'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function save(PurchaseOrderService $service): void
    {
        $this->validate();

        $po = $service->create(
            data: ['supplier_id' => $this->supplier_id, 'tanggal_po' => $this->tanggal_po],
            items: $this->items,
            userId: auth()->id(),
        );

        session()->flash('success', "PO {$po->no_po} berhasil dibuat sebagai draft.");
        $this->redirectRoute('purchase-order.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.purchasing.purchase-order.form', [
            'supplierOptions' => Supplier::where('is_active', true)->orderBy('nama_pbf')->get(),
            'obatOptions' => Obat::where('is_active', true)->orderBy('nama_obat')->get(),
        ]);
    }
}
