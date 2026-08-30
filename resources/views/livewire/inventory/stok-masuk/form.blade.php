<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="mb-4 text-lg font-semibold text-slate-800">Stok Masuk</h1>

    <form wire:submit="save" class="max-w-xl space-y-4 rounded-md border border-slate-200 p-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Obat *</label>
            <select wire:model="obat_id" class="w-full rounded-md border-slate-300 text-sm">
                <option value="">-- Pilih Obat --</option>
                @foreach ($obatOptions as $obat)
                    <option value="{{ $obat->id }}">{{ $obat->kode_obat }} — {{ $obat->nama_obat }}</option>
                @endforeach
            </select>
            @error('obat_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Supplier / PBF *</label>
            <select wire:model="supplier_id" class="w-full rounded-md border-slate-300 text-sm">
                <option value="">-- Pilih Supplier --</option>
                @foreach ($supplierOptions as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->nama_pbf }}</option>
                @endforeach
            </select>
            @error('supplier_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">No. Batch *</label>
                <input type="text" wire:model="no_batch" class="w-full rounded-md border-slate-300 text-sm">
                <p class="mt-1 text-xs text-slate-400">Jika no. batch ini sudah ada untuk obat tsb, stok akan ditambahkan ke batch yang sama.</p>
                @error('no_batch') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Jumlah (satuan dasar) *</label>
                <input type="number" min="1" wire:model="jumlah" class="w-full rounded-md border-slate-300 text-sm">
                @error('jumlah') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal Produksi</label>
                <input type="date" wire:model="tanggal_produksi" class="w-full rounded-md border-slate-300 text-sm">
                @error('tanggal_produksi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal Kadaluarsa *</label>
                <input type="date" wire:model="tanggal_kadaluarsa" class="w-full rounded-md border-slate-300 text-sm">
                @error('tanggal_kadaluarsa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">Harga Beli (per satuan dasar) *</label>
                <input type="number" step="0.01" min="0" wire:model="harga_beli" class="w-full rounded-md border-slate-300 text-sm">
                @error('harga_beli') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Keterangan</label>
            <input type="text" wire:model="keterangan" placeholder="mis. No. faktur supplier" class="w-full rounded-md border-slate-300 text-sm">
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="save" wire:loading.class="opacity-60 cursor-wait" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Simpan Stok Masuk
        </button>
    </form>
</div>
