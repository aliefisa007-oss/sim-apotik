<?php

<?php

namespace App\Livewire\Purchasing\PenerimaanBarang;

use App\Models\BatchObat;
use App\Models\Obat;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PenerimaanBarangService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Form extends Component
{
    #[Url]
    public ?int $po_id = null;

    public ?PurchaseOrder $po = null;

    public ?int $supplier_id = null;
    public string $tanggal_terima = '';
    public string $no_faktur_supplier = '';

    /** @var array<int, array{obat_id: ?int, no_batch: string, tanggal_produksi: ?string, tanggal_kadaluarsa: string, harga_beli: ?float, jumlah: ?int}> */
    public array $items = [];

    public function mount(): void
    {
        $this->authorize('create', BatchObat::class);

        $this->tanggal_terima = now()->toDateString();

        if ($this->po_id) {
            $this->po = PurchaseOrder::with('detail.obat')->findOrFail($this->po_id);
            $this->supplier_id = $this->po->supplier_id;

            foreach ($this->po->detail as $detailPo) {
                if ($detailPo->sisaJumlah() <= 0) {
                    continue;
                }
                $this->items[] = [
                    'obat_id' => $detailPo->obat_id,
                    'no_batch' => '',
                    'tanggal_produksi' => null,
                    'tanggal_kadaluarsa' => '',
                    'harga_beli' => (float) $detailPo->harga_satuan,
                    'jumlah' => $detailPo->sisaJumlah(),
                ];
            }
        } else {
            $this->addItem();
        }
    }

    public function addItem(): void
    {
        $this->items[] = ['obat_id' => null, 'no_batch' => '', 'tanggal_produksi' => null, 'tanggal_kadaluarsa' => '', 'harga_beli' => null, 'jumlah' => null];
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
            'tanggal_terima' => ['required', 'date'],
            'no_faktur_supplier' => ['nullable', 'string', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.no_batch' => ['required', 'string', 'max:100'],
            'items.*.tanggal_produksi' => ['nullable', 'date'],
            'items.*.tanggal_kadaluarsa' => ['required', 'date', 'after:today'],
            'items.*.harga_beli' => ['required', 'numeric', 'min:0'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function save(PenerimaanBarangService $service): void
    {
        $this->validate();

        try {
            $penerimaan = $service->receive(
                data: [
                    'po_id' => $this->po_id,
                    'supplier_id' => $this->supplier_id,
                    'tanggal_terima' => $this->tanggal_terima,
                    'no_faktur_supplier' => $this->no_faktur_supplier ?: null,
                ],
                items: $this->items,
                userId: auth()->id(),
            );
        } catch (\InvalidArgumentException $e) {
            $this->addError('items', $e->getMessage());
            return;
        }

        session()->flash('success', "Penerimaan barang berhasil dicatat ({$penerimaan->detail->count()} item, stok bertambah).");
        $this->redirectRoute('penerimaan-barang.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.purchasing.penerimaan-barang.form', [
            'supplierOptions' => Supplier::where('is_active', true)->orderBy('nama_pbf')->get(),
            'obatOptions' => Obat::where('is_active', true)->orderBy('nama_obat')->get(),
        ]);
    }
}
