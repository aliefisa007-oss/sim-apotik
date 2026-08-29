<?php

namespace App\Livewire\Inventory\StokMasuk;

use App\Models\Obat;
use App\Models\Supplier;
use App\Services\StockService;
use Livewire\Component;

/**
 * Form penerimaan stok LANGSUNG (tanpa PO), dipakai sampai Phase 5
 * (PurchaseOrderService + PenerimaanBarangService) selesai. Setelah Phase 5,
 * ini tetap berguna untuk kasus non-PO seperti stok awal / donasi / koreksi
 * penerimaan darurat.
 */
class Form extends Component
{
    public ?int $obat_id = null;
    public ?int $supplier_id = null;
    public string $no_batch = '';
    public ?string $tanggal_produksi = null;
    public string $tanggal_kadaluarsa = '';
    public ?float $harga_beli = null;
    public ?int $jumlah = null;
    public string $keterangan = '';

    public function mount(): void
    {
        $this->authorize('create', \App\Models\BatchObat::class);
    }

    protected function rules(): array
    {
        return [
            'obat_id' => ['required', 'exists:obat,id'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'no_batch' => ['required', 'string', 'max:100'],
            'tanggal_produksi' => ['nullable', 'date', 'before_or_equal:tanggal_kadaluarsa'],
            'tanggal_kadaluarsa' => ['required', 'date', 'after:today'],
            'harga_beli' => ['required', 'numeric', 'min:0'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ];
    }

    public function save(StockService $service): void
    {
        $this->validate();

        $batch = $service->receiveStock(
            data: [
                'obat_id' => $this->obat_id,
                'supplier_id' => $this->supplier_id,
                'no_batch' => $this->no_batch,
                'tanggal_produksi' => $this->tanggal_produksi,
                'tanggal_kadaluarsa' => $this->tanggal_kadaluarsa,
                'harga_beli' => $this->harga_beli,
                'jumlah' => $this->jumlah,
            ],
            userId: auth()->id(),
            keterangan: $this->keterangan ?: 'Penerimaan stok manual',
        );

        session()->flash('success', "Stok masuk dicatat untuk batch {$batch->no_batch}. Stok saat ini: {$batch->stok_saat_ini}.");

        $this->reset(['no_batch', 'tanggal_produksi', 'tanggal_kadaluarsa', 'harga_beli', 'jumlah', 'keterangan']);
    }

    public function render()
    {
        return view('livewire.inventory.stok-masuk.form', [
            'obatOptions' => Obat::where('is_active', true)->orderBy('nama_obat')->get(),
            'supplierOptions' => Supplier::where('is_active', true)->orderBy('nama_pbf')->get(),
        ]);
    }
}
