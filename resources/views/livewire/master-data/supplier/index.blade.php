<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Supplier / PBF</h1>
        @can('create', \App\Models\Supplier::class)
            <button wire:click="openCreate" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                + Tambah Supplier
            </button>
        @endcan
    </div>

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama PBF..."
           class="mb-3 w-72 rounded-md border-slate-300 text-sm">

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Nama PBF</th>
                    <th class="px-3 py-2">No. Izin</th>
                    <th class="px-3 py-2">Kontak</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($supplierList as $supplier)
                    <tr wire:key="supplier-{{ $supplier->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-medium text-slate-800">{{ $supplier->nama_pbf }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $supplier->no_izin_pbf ?? '-' }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $supplier->kontak ?? '-' }}</td>
                        <td class="px-3 py-2 text-center">
                            @if ($supplier->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @can('update', $supplier)
                                <button wire:click="openEdit({{ $supplier->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-900">Ubah</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-10 text-center text-sm text-slate-500">
                            Belum ada supplier/PBF. Tambahkan supplier pertama untuk mulai proses purchasing nanti.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $supplierList->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/30" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-md bg-white p-5 shadow-lg">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">
                    {{ $editing ? 'Ubah Supplier' : 'Tambah Supplier' }}
                </h2>
                <form wire:submit="save" class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Nama PBF *</label>
                        <input type="text" wire:model="nama_pbf" autofocus class="w-full rounded-md border-slate-300 text-sm">
                        @error('nama_pbf') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">No. Izin PBF</label>
                        <input type="text" wire:model="no_izin_pbf" class="w-full rounded-md border-slate-300 text-sm">
                        @error('no_izin_pbf') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Alamat</label>
                        <textarea wire:model="alamat" rows="2" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Kontak</label>
                        <input type="text" wire:model="kontak" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="supplier_is_active" class="rounded border-slate-300">
                        <label for="supplier_is_active" class="text-sm text-slate-700">Aktif</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-md px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
