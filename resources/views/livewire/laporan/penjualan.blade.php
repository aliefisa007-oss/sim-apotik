<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Laporan Penjualan</h1>

    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Dari Tanggal</label>
            <input type="date" wire:model.live="tanggalMulai" class="rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Sampai Tanggal</label>
            <input type="date" wire:model.live="tanggalSelesai" class="rounded-md border-slate-300 text-sm">
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Transaksi</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">{{ $ringkasan['total_transaksi'] }}</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Total Omzet</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['total_omzet'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Rata-rata / Transaksi</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['rata_rata_per_transaksi'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Grafik tren omzet — CSS bar chart sederhana, tanpa dependency JS baru --}}
    @if ($omzetPerHari->isNotEmpty())
        @php $maxOmzet = max($omzetPerHari->pluck('omzet')->toArray()) ?: 1; @endphp
        <div class="mb-6 rounded-md border border-slate-200 bg-white p-4">
            <p class="mb-3 text-sm font-semibold text-slate-700">Tren Omzet Harian</p>
            <div class="flex h-40 items-end gap-1">
                @foreach ($omzetPerHari as $row)
                    <div class="group relative flex-1">
                        <div
                            class="mx-auto w-full rounded-t bg-slate-700 transition hover:bg-slate-900"
                            style="height: {{ max(4, ($row['omzet'] / $maxOmzet) * 100) }}%"
                        ></div>
                        <div class="pointer-events-none absolute -top-8 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-800 px-2 py-1 text-xs text-white group-hover:block">
                            {{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m') }}: Rp {{ number_format($row['omzet'], 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Obat terlaris --}}
    <div class="mb-6 overflow-hidden rounded-md border border-slate-200">
        <div class="border-b border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
            Obat Terlaris
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Obat</th>
                    <th class="px-3 py-2 text-right">Jumlah Terjual</th>
                    <th class="px-3 py-2 text-right">Total Omzet</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($obatTerlaris as $row)
                    <tr>
                        <td class="px-3 py-2 text-slate-800">{{ $row['nama_obat'] }}</td>
                        <td class="px-3 py-2 text-right text-slate-600">{{ $row['jumlah_terjual'] }}</td>
                        <td class="px-3 py-2 text-right font-medium text-slate-800">Rp {{ number_format($row['total_omzet'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-3 py-6 text-center text-sm text-slate-500">Tidak ada data pada rentang ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Daftar transaksi --}}
    <div class="overflow-hidden rounded-md border border-slate-200">
        <div class="border-b border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
            Daftar Transaksi
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">No. Transaksi</th>
                    <th class="px-3 py-2">Kasir</th>
                    <th class="px-3 py-2">Resep</th>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($daftarTransaksi as $t)
                    <tr>
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $t->no_transaksi }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $t->kasir->name }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $t->resep->no_resep ?? '-' }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-right font-medium text-slate-800">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">Tidak ada transaksi pada rentang ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
