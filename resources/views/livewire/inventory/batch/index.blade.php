<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Batch Obat</h1>

    <select
        onchange="window.location.href = '{{ route('batch.index') }}?obat_id=' + this.value"
        class="mb-4 w-96 rounded-md border-slate-300 text-sm"
    >
        <option value="">-- Pilih Obat --</option>
        @foreach ($obatOptions as $option)
            <option value="{{ $option->id }}" {{ $obat && $obat->id === $option->id ? 'selected' : '' }}>
                {{ $option->kode_obat }} — {{ $option->nama_obat }}
            </option>
        @endforeach
    </select>

    @if ($obat)
        <div class="overflow-hidden rounded-md border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2">Batch</th>
                        <th class="px-3 py-2">Kadaluarsa</th>
                        <th class="px-3 py-2">Supplier</th>
                        <th class="px-3 py-2 text-right">Harga Beli</th>
                        <th class="px-3 py-2 text-right">Harga Jual</th>
                        <th class="px-3 py-2 text-right">Stok</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($batches as $batch)
                        @php
                            $expiryStyles = [
                                'aman' => 'bg-emerald-100 text-emerald-700',
                                'perhatian' => 'bg-sky-100 text-sky-700',
                                'mendekati_expired' => 'bg-amber-100 text-amber-700',
                                'kritis' => 'bg-orange-100 text-orange-700',
                                'expired' => 'bg-red-100 text-red-700',
                            ];
                            $expiryLabels = [
                                'aman' => 'Aman',
                                'perhatian' => 'Perhatian (< 90 hari)',
                                'mendekati_expired' => 'Mendekati Expired (< 30 hari)',
                                'kritis' => 'Kritis (< 7 hari)',
                                'expired' => 'Expired',
                            ];
                            $status = $batch->expiryStatus();
                        @endphp
                        <tr wire:key="batch-{{ $batch->id }}" class="hover:bg-slate-50">
                            <td class="px-3 py-2 font-mono text-xs text-slate-700">{{ $batch->no_batch }}</td>
                            <td class="px-3 py-2">
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $expiryStyles[$status] }}">
                                    {{ $batch->tanggal_kadaluarsa->format('d M Y') }} — {{ $expiryLabels[$status] }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-slate-600">{{ $batch->supplier->nama_pbf }}</td>
                            <td class="px-3 py-2 text-right text-slate-600">Rp{{ number_format($batch->harga_beli, 0, ',', '.') }}</td>
                            <td class="px-3 py-2 text-right text-slate-600">
                                {{ $batch->harga_jual ? 'Rp' . number_format($batch->harga_jual, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-3 py-2 text-right font-medium text-slate-800">{{ $batch->stok_saat_ini }}</td>
                            <td class="px-3 py-2 capitalize text-slate-600">{{ $batch->status }}</td>
                            <td class="px-3 py-2 text-right">
                                @can('update', $batch)
                                    <a href="{{ route('hja-kalkulator.edit', $batch) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">Set Harga Jual</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-sm text-slate-500">
                                Belum ada batch untuk obat ini. Gunakan menu Stok Masuk untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <p class="text-sm text-slate-500">Pilih obat untuk melihat daftar batch-nya.</p>
    @endif
</div>
