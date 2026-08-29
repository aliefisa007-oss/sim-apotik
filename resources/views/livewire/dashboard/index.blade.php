<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Dashboard</h1>

    {{-- Kartu ringkasan --}}
    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Penjualan Hari Ini</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['penjualan_hari_ini']['total_omzet'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $ringkasan['penjualan_hari_ini']['total_transaksi'] }} transaksi</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Omzet Bulan Ini</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['penjualan_bulan_ini']['total_omzet'], 0, ',', '.') }}</p>
            <p class="mt-1 text-xs text-slate-400">{{ $ringkasan['penjualan_bulan_ini']['total_transaksi'] }} transaksi</p>
        </div>
        <div class="rounded-md border border-amber-200 bg-amber-50 p-4">
            <p class="text-xs text-amber-700">Stok Menipis</p>
            <p class="mt-1 text-xl font-semibold text-amber-800">{{ $ringkasan['stok_menipis'] }} obat</p>
            <a href="{{ route('laporan.stok') }}" wire:navigate class="mt-1 inline-block text-xs text-amber-700 underline">Lihat detail</a>
        </div>
        <div class="rounded-md border border-sky-200 bg-sky-50 p-4">
            <p class="text-xs text-sky-700">Resep Menunggu Verifikasi</p>
            <p class="mt-1 text-xl font-semibold text-sky-800">{{ $ringkasan['resep_menunggu_verifikasi'] }}</p>
            <a href="{{ route('resep.index') }}" wire:navigate class="mt-1 inline-block text-xs text-sky-700 underline">Verifikasi sekarang</a>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2">
        <div class="rounded-md border border-red-200 bg-red-50 p-4">
            <p class="text-xs text-red-700">Batch Mendekati Kadaluarsa (30 hari)</p>
            <p class="mt-1 text-xl font-semibold text-red-800">{{ $ringkasan['batch_mendekati_kadaluarsa'] }} batch</p>
            <a href="{{ route('laporan.stok') }}" wire:navigate class="mt-1 inline-block text-xs text-red-700 underline">Lihat detail</a>
        </div>
        @if (auth()->user()->hasRole('owner'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs text-emerald-700">Laba Kotor Bulan Ini</p>
                <p class="mt-1 text-xl font-semibold text-emerald-800">Rp {{ number_format($ringkasan['keuangan_bulan_ini']['laba_kotor'], 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-emerald-600">Margin {{ number_format($ringkasan['keuangan_bulan_ini']['margin_persen'], 1) }}%</p>
            </div>
        @endif
    </div>

    {{-- Transaksi terbaru --}}
    <div class="overflow-hidden rounded-md border border-slate-200">
        <div class="border-b border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
            Transaksi Terbaru
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">No. Transaksi</th>
                    <th class="px-3 py-2">Kasir</th>
                    <th class="px-3 py-2">Waktu</th>
                    <th class="px-3 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($ringkasan['transaksi_terbaru'] as $t)
                    <tr>
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $t->no_transaksi }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $t->kasir->name }}</td>
                        <td class="px-3 py-2 text-slate-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-right font-medium text-slate-800">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-sm text-slate-500">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
