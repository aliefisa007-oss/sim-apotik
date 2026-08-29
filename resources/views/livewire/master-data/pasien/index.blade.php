<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Data Pasien</h1>
        @can('create', \App\Models\Pasien::class)
            <button
                type="button"
                wire:click="openCreate"
                class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700"
            >
                + Tambah Pasien
            </button>
        @endcan
    </div>

    <div class="mb-3">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari nama pasien atau no. RM..."
            class="w-72 rounded-md border-slate-300 text-sm focus:border-slate-500 focus:ring-slate-500"
        >
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">No. RM</th>
                    <th class="px-3 py-2">Nama Pasien</th>
                    <th class="px-3 py-2">Tgl. Lahir</th>
                    <th class="px-3 py-2">No. Telepon</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($pasienList as $pasien)
                    <tr wire:key="pasien-{{ $pasien->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $pasien->no_rm ?? '-' }}</td>
                        <td class="px-3 py-2 font-medium text-slate-800">{{ $pasien->nama_pasien }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $pasien->tanggal_lahir?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $pasien->no_telepon ?? '-' }}</td>
                        <td class="px-3 py-2 text-center">
                            @if ($pasien->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @can('update', $pasien)
                                <button type="button" wire:click="openEdit({{ $pasien->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-900">Ubah</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-10 text-center text-sm text-slate-500">Belum ada data pasien.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $pasienList->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/30">
            <div class="w-full max-w-lg rounded-md bg-white p-5 shadow-lg">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">
                    {{ $editing ? 'Ubah Pasien' : 'Tambah Pasien' }}
                </h2>

                <div class="grid grid-cols-2 gap-3">
                    <div class="col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Nama Pasien *</label>
                        <input type="text" wire:model="nama_pasien" class="w-full rounded-md border-slate-300 text-sm">
                        @error('nama_pasien') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">No. Rekam Medis</label>
                        <input type="text" wire:model="no_rm" class="w-full rounded-md border-slate-300 text-sm">
                        @error('no_rm') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal Lahir</label>
                        <input type="date" wire:model="tanggal_lahir" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Jenis Kelamin</label>
                        <select wire:model="jenis_kelamin" class="w-full rounded-md border-slate-300 text-sm">
                            <option value="">-</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">No. Telepon</label>
                        <input type="text" wire:model="no_telepon" class="w-full rounded-md border-slate-300 text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Alamat</label>
                        <textarea wire:model="alamat" rows="2" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Catatan Alergi</label>
                        <textarea wire:model="alergi" rows="2" placeholder="mis. alergi penisilin — ditampilkan ke apoteker saat verifikasi resep" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                    </div>
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showModal', false)" class="rounded-md px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100">Batal</button>
                    <button type="button" wire:click="save" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
