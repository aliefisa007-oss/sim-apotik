<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Laporan Stok</h1>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Nilai Persediaan (harga beli)</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format($nilaiPersediaan, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-md border border-red-200 bg-red-50 p-4">
            <p class="text-xs text-red-700">Batch Mendekati Kadaluarsa ({{ $dalamHari }} hari)</p>
            <p class="mt-1 text-xl font-semibold text-red-800">{{ $batchMendekatiKadaluarsa->count() }} batch</p>
        </div>
    </div>

    {{-- Stok saat ini per obat --}}
    <div class="mb-6">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Stok per Obat</h2>
            <label class="flex items-center gap-2 text-xs text-slate-600">
                <input type="checkbox" wire:model.live="hanyaMenipis" class="rounded border-slate-300">
                Tampilkan yang menipis saja
            </label>
        </div>

        <div class="overflow-hidden rounded-md border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Kode</th>
                        <th class="px-3 py-2">Nama Obat</th>
                        <th class="px-3 py-2 text-right">Stok Saat Ini</th>
                        <th class="px-3 py-2 text-right">Ambang Minimum</th>
                        <th class="px-3 py-2 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($stokSaatIni as $row)
                        <tr class="{{ $row['menipis'] ? 'bg-amber-50' : '' }}">
                            <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $row['kode_obat'] }}</td>
                            <td class="px-3 py-2 text-slate-800">{{ $row['nama_obat'] }}</td>
                            <td class="px-3 py-2 text-right text-slate-600">{{ $row['stok_total'] }}</td>
                            <td class="px-3 py-2 text-right text-slate-500">{{ $row['stok_minimum'] }}</td>
                            <td class="px-3 py-2 text-center">
                                @if ($row['menipis'])
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">Menipis</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Aman</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Batch mendekati kadaluarsa --}}
    <div>
        <h2 class="mb-2 text-sm font-semibold text-slate-700">Batch Mendekati Kadaluarsa</h2>
        <div class="overflow-hidden rounded-md border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Obat</th>
                        <th class="px-3 py-2">No. Batch</th>
                        <th class="px-3 py-2 text-right">Sisa Stok</th>
                        <th class="px-3 py-2">Tgl. Kadaluarsa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($batchMendekatiKadaluarsa as $batch)
                        <tr>
                            <td class="px-3 py-2 text-slate-800">{{ $batch->obat->nama_obat }}</td>
                            <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $batch->no_batch }}</td>
                            <td class="px-3 py-2 text-right text-slate-600">{{ $batch->stok_saat_ini }}</td>
                            <td class="px-3 py-2 text-red-600">{{ $batch->tanggal_kadaluarsa->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-center text-sm text-slate-500">Tidak ada batch mendekati kadaluarsa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
