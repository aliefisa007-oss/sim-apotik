<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">
        Kartu Stok {{ $obat ? "— {$obat->nama_obat}" : '' }}
    </h1>

    <div class="mb-3 flex flex-wrap items-end gap-2">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Obat</label>
            <select
                onchange="window.location.href = '{{ route('kartu-stok.index') }}?obat_id=' + this.value"
                class="w-64 rounded-md border-slate-300 text-sm"
            >
                <option value="">-- Semua Obat --</option>
                @foreach ($obatOptions as $option)
                    <option value="{{ $option->id }}" {{ $obat && $obat->id === $option->id ? 'selected' : '' }}>
                        {{ $option->nama_obat }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Jenis</label>
            <select wire:model.live="jenisFilter" class="w-56 rounded-md border-slate-300 text-sm">
                <option value="">-- Semua Jenis --</option>
                @foreach ($jenisOptions as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Dari</label>
            <input type="date" wire:model.live="dari" class="rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Sampai</label>
            <input type="date" wire:model.live="sampai" class="rounded-md border-slate-300 text-sm">
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2">Obat</th>
                    <th class="px-3 py-2">Batch</th>
                    <th class="px-3 py-2">Jenis</th>
                    <th class="px-3 py-2 text-right">Jumlah</th>
                    <th class="px-3 py-2 text-right">Saldo Sebelum</th>
                    <th class="px-3 py-2 text-right">Saldo Sesudah</th>
                    <th class="px-3 py-2">Oleh</th>
                    <th class="px-3 py-2">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($riwayat as $entry)
                    <tr wire:key="kartu-{{ $entry->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 text-slate-600">{{ $entry->created_at->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $entry->obat->nama_obat }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $entry->batch->no_batch }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ str_replace('_', ' ', $entry->jenis_transaksi) }}</td>
                        <td class="px-3 py-2 text-right font-medium {{ $entry->jumlah >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                            {{ $entry->jumlah >= 0 ? '+' : '' }}{{ $entry->jumlah }}
                        </td>
                        <td class="px-3 py-2 text-right text-slate-500">{{ $entry->saldo_sebelum }}</td>
                        <td class="px-3 py-2 text-right text-slate-700">{{ $entry->saldo_sesudah }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $entry->user->name ?? 'Sistem (Otomatis)' }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $entry->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-10 text-center text-sm text-slate-500">
                            Belum ada riwayat mutasi stok untuk filter ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $riwayat->links() }}</div>
</div>
