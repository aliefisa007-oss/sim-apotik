<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Buat Purchase Order</h1>

    <form wire:submit="save" class="max-w-3xl space-y-4">
        <div class="grid grid-cols-2 gap-4 rounded-md border border-slate-200 p-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Supplier *</label>
                <select wire:model="supplier_id" class="w-full rounded-md border-slate-300 text-sm">
                    <option value="">-- Pilih Supplier --</option>
                    @foreach ($supplierOptions as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->nama_pbf }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal PO *</label>
                <input type="date" wire:model="tanggal_po" class="w-full rounded-md border-slate-300 text-sm">
                @error('tanggal_po') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="rounded-md border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Item</h2>
                <button type="button" wire:click="addItem" class="text-xs font-medium text-slate-600 underline">+ Tambah Item</button>
            </div>

            @error('items') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="space-y-2">
                @foreach ($items as $index => $item)
                    <div wire:key="po-item-{{ $index }}" class="grid grid-cols-12 gap-2">
                        <div class="col-span-5">
                            <select wire:model="items.{{ $index }}.obat_id" class="w-full rounded-md border-slate-300 text-sm">
                                <option value="">-- Obat --</option>
                                @foreach ($obatOptions as $obat)
                                    <option value="{{ $obat->id }}">{{ $obat->nama_obat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-3">
                            <input type="number" min="1" wire:model="items.{{ $index }}.jumlah_order" placeholder="Jumlah" class="w-full rounded-md border-slate-300 text-sm">
                        </div>
                        <div class="col-span-3">
                            <input type="number" step="0.01" min="0" wire:model="items.{{ $index }}.harga_satuan" placeholder="Harga satuan" class="w-full rounded-md border-slate-300 text-sm">
                        </div>
                        <div class="col-span-1 text-right">
                            @if (count($items) > 1)
                                <button type="button" wire:click="removeItem({{ $index }})" class="text-xs text-red-600">Hapus</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan sebagai Draft</button>
            <a href="{{ route('purchase-order.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
        </div>
    </form>
</div>
