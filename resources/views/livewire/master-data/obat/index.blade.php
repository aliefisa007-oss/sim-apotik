<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Data Obat</h1>
        @can('create', \App\Models\Obat::class)
            <a href="{{ route('obat.create') }}"
               class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                + Tambah Obat
            </a>
        @endcan
    </div>

    <div class="mb-3 flex flex-wrap gap-2">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama, generik, kode, atau barcode..."
            autofocus
            class="w-72 rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"
        >

        <select wire:model.live="golonganFilter" class="rounded-md border-slate-300 text-sm">
            <option value="">Semua Golongan</option>
            @foreach ($golonganOptions as $golongan)
                <option value="{{ $golongan }}">{{ ucwords(str_replace('_', ' ', $golongan)) }}</option>
            @endforeach
        </select>

        <select wire:model.live="kategoriFilter" class="rounded-md border-slate-300 text-sm">
            <option value="">Semua Kategori</option>
            @foreach ($kategoriOptions as $kategori)
                <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Kode</th>
                    <th class="px-3 py-2">Nama Obat</th>
                    <th class="px-3 py-2">Generik</th>
                    <th class="px-3 py-2">Kategori</th>
                    <th class="px-3 py-2">Golongan</th>
                    <th class="px-3 py-2">Satuan Dasar</th>
                    <th class="px-3 py-2">Barcode</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($obatList as $obat)
                    <tr wire:key="obat-{{ $obat->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $obat->kode_obat }}</td>
                        <td class="px-3 py-2 font-medium text-slate-800">{{ $obat->nama_obat }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $obat->nama_generik ?? '-' }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $obat->kategori->nama }}</td>
                        <td class="px-3 py-2">
                            <x-badge-golongan :golongan="$obat->golongan" />
                        </td>
                        <td class="px-3 py-2 text-slate-600">{{ $obat->satuanDasar->nama }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $obat->barcode ?? '-' }}</td>
                        <td class="px-3 py-2 text-center">
                            @if ($obat->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('obat.edit', $obat) }}" class="text-slate-600 hover:text-slate-900 text-xs font-medium">Ubah</a>
                            @can('deactivate', $obat)
                                @if ($obat->is_active)
                                    <button
                                        type="button"
                                        wire:click="deactivate({{ $obat->id }})"
                                        wire:confirm="Nonaktifkan obat {{ $obat->kode_obat }}?"
                                        class="ml-2 text-red-600 hover:text-red-800 text-xs font-medium"
                                    >Nonaktifkan</button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-10 text-center text-sm text-slate-500">
                            <p class="mb-2">Belum ada obat yang cocok dengan pencarian.</p>
                            @can('create', \App\Models\Obat::class)
                                <a href="{{ route('obat.create') }}" class="text-slate-700 underline">+ Tambah Obat</a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $obatList->links() }}
    </div>
</div>
