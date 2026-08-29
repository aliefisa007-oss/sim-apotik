<div>
    <h1 class="mb-1 text-lg font-semibold text-slate-800">Terima Barang</h1>
    @if ($po)
        <p class="mb-4 text-sm text-slate-500">Terhubung ke PO {{ $po->no_po }} — {{ $po->supplier->nama_pbf }}</p>
    @else
        <p class="mb-4 text-sm text-slate-500">Penerimaan tanpa PO (mis. stok awal / koreksi darurat)</p>
    @endif

    <form wire:submit="save" class="max-w-3xl space-y-4">
        <div class="grid grid-cols-3 gap-4 rounded-md border border-slate-200 p-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Supplier *</label>
                <select wire:model="supplier_id" {{ $po ? 'disabled' : '' }} class="w-full rounded-md border-slate-300 text-sm">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach ($supplierOptions as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama_pbf }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal Terima *</label>
                <input type="date" wire:model="tanggal_terima" class="w-full rounded-md border-slate-300 text-sm">
                @error('tanggal_terima') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">No. Faktur Supplier</label>
                <input type="text" wire:model="no_faktur_supplier" class="w-full rounded-md border-slate-300 text-sm">
            </div>
        </div>

        <div class="rounded-md border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Item Diterima</h2>
                @unless ($po)
                    <button type="button" wire:click="addItem" class="text-xs font-medium text-slate-600 underline">+ Tambah Item</button>
                @endunless
            </div>

            @error('items') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="space-y-3">
                @foreach ($items as $index => $item)
                    <div wire:key="penerimaan-item-{{ $index }}" class="grid grid-cols-12 gap-2 border-b border-slate-100 pb-2">
                        <div class="col-span-3">
                            <select wire:model="items.{{ $index }}.obat_id" {{ $po ? 'disabled' : '' }} class="w-full rounded-md border-slate-300 text-sm">
                                <option value="">-- Obat --</option>
                                @foreach ($obatOptions as $obat)
                                    <option value="{{ $obat->id }}">{{ $obat->nama_obat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <input type="text" wire:model="items.{{ $index }}.no_batch" placeholder="No. Batch" class="w-full rounded-md border-slate-300 text-sm">
                        </div>
                        <div class="col-span-2">
                            <input type="date" wire:model="items.{{ $index }}.tanggal_kadaluarsa" class="w-full rounded-md border-slate-300 text-sm">
                        </div>
                        <div class="col-span-2">
                            <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.harga_beli" placeholder="Harga beli" class="w-full rounded-md border-slate-300 text-sm">
                        </div>
                        <div class="col-span-2">
                            <input type="number" min="1" wire:model="items.{{ $index }}.jumlah" placeholder="Jumlah" class="w-full rounded-md border-slate-300 text-sm">
                        </div>
                        <div class="col-span-1 text-right">
                            @unless ($po)
                                @if (count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-xs text-red-600">Hapus</button>
                                @endif
                            @endunless
                        </div>
                        @error("items.{$index}.no_batch") <p class="col-span-12 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error("items.{$index}.tanggal_kadaluarsa") <p class="col-span-12 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan Penerimaan</button>
            <a href="{{ route('penerimaan-barang.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
        </div>
    </form>
</div>
