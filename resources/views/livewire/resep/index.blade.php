<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Resep</h1>
        @can('create', \App\Models\Resep::class)
            <a href="{{ route('resep.create') }}" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                + Input Resep
            </a>
        @endcan
    </div>

    <div class="mb-3 flex flex-wrap gap-2">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Cari no. resep atau nama pasien..."
            class="w-72 rounded-md border-slate-300 text-sm"
        >
        <select wire:model.live="statusFilter" class="rounded-md border-slate-300 text-sm">
            <option value="">Semua Status</option>
            @foreach ($statusOptions as $status)
                <option value="{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">No. Resep</th>
                    <th class="px-3 py-2">Pasien</th>
                    <th class="px-3 py-2">Dokter</th>
                    <th class="px-3 py-2">Tgl. Resep</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2">Diverifikasi Oleh</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($resepList as $resep)
                    <tr wire:key="resep-{{ $resep->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $resep->no_resep }}</td>
                        <td class="px-3 py-2 font-medium text-slate-800">{{ $resep->pasien->nama_pasien }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $resep->nama_dokter }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $resep->tanggal_resep->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-center">
                            @php
                                $statusColor = match ($resep->status) {
                                    'menunggu_verifikasi' => 'bg-amber-100 text-amber-700',
                                    'terverifikasi' => 'bg-sky-100 text-sky-700',
                                    'selesai' => 'bg-emerald-100 text-emerald-700',
                                    'ditolak' => 'bg-red-100 text-red-700',
                                    default => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <span class="rounded-full {{ $statusColor }} px-2 py-0.5 text-xs font-medium">
                                {{ ucwords(str_replace('_', ' ', $resep->status)) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-slate-500">{{ $resep->apotekerVerifikasi?->name ?? '-' }}</td>
                        <td class="px-3 py-2 text-right">
                            @can('verify', \App\Models\Resep::class)
                                @if ($resep->status === 'menunggu_verifikasi')
                                    <button type="button" wire:click="openVerifikasi({{ $resep->id }}, 'verify')" class="text-xs font-medium text-emerald-600 hover:text-emerald-800">Verifikasi</button>
                                    <button type="button" wire:click="openVerifikasi({{ $resep->id }}, 'reject')" class="ml-2 text-xs font-medium text-red-600 hover:text-red-800">Tolak</button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">Belum ada resep.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $resepList->links() }}</div>

    @if ($showVerifikasiModal && $reviewing)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/30">
            <div class="w-full max-w-lg rounded-md bg-white p-5 shadow-lg">
                <h2 class="mb-1 text-sm font-semibold text-slate-800">
                    {{ $aksi === 'reject' ? 'Tolak Resep' : 'Verifikasi Resep' }} — {{ $reviewing->no_resep }}
                </h2>
                <p class="mb-3 text-xs text-slate-500">
                    Pasien: {{ $reviewing->pasien->nama_pasien }} · Dokter: {{ $reviewing->nama_dokter }}
                    @if ($reviewing->pasien->alergi)
                        <span class="block font-medium text-red-600">Alergi: {{ $reviewing->pasien->alergi }}</span>
                    @endif
                </p>

                @error('verifikasi') <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</div> @enderror

                <div class="mb-3 overflow-hidden rounded-md border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-xs">
                        <thead class="bg-slate-50 text-left uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-2 py-1.5">Obat</th>
                                <th class="px-2 py-1.5">Jumlah</th>
                                <th class="px-2 py-1.5">Aturan Pakai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($reviewing->detail as $d)
                                <tr>
                                    <td class="px-2 py-1.5">{{ $d->obat->nama_obat }}</td>
                                    <td class="px-2 py-1.5">{{ $d->jumlah_diresepkan }}</td>
                                    <td class="px-2 py-1.5">{{ $d->aturan_pakai ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($aksi === 'reject')
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Alasan Penolakan *</label>
                        <textarea wire:model="alasanTolak" rows="2" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                        @error('alasanTolak') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Catatan Verifikasi (opsional)</label>
                        <textarea wire:model="catatanVerifikasi" rows="2" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                    </div>
                @endif

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showVerifikasiModal', false)" class="rounded-md px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100">Batal</button>
                    <button
                        type="button"
                        wire:click="confirmVerifikasi"
                        class="rounded-md px-4 py-2 text-sm font-medium text-white {{ $aksi === 'reject' ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700' }}"
                    >
                        {{ $aksi === 'reject' ? 'Tolak Resep' : 'Verifikasi' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
