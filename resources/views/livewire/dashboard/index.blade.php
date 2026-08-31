<div>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Dashboard</h1>
            <p class="text-sm text-slate-500">Halo, {{ auth()->user()->name }} — ringkasan per {{ now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    {{-- Kartu ringkasan utama --}}
    <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.5-3.75A1.5 1.5 0 015.13 3.5h13.74a1.5 1.5 0 011.38 1l1.5 3.75M12 15a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-xs text-slate-500">Penjualan Hari Ini</p>
                <p class="mt-1 truncate text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['penjualan_hari_ini']['total_omzet'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-xs text-slate-400">{{ $ringkasan['penjualan_hari_ini']['total_transaksi'] }} transaksi</p>
            </div>
        </div>

        <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-600">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-xs text-slate-500">Omzet Bulan Ini</p>
                <p class="mt-1 truncate text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['penjualan_bulan_ini']['total_omzet'], 0, ',', '.') }}</p>
                <p class="mt-0.5 text-xs text-slate-400">{{ $ringkasan['penjualan_bulan_ini']['total_transaksi'] }} transaksi</p>
            </div>
        </div>

        <a href="{{ route('laporan.stok') }}" wire:navigate class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm transition-colors hover:bg-amber-100">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-8.25 3.75h.008v.008h-.008v-.008z" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-xs text-amber-700">Stok Menipis</p>
                <p class="mt-1 text-xl font-semibold text-amber-800">{{ $ringkasan['stok_menipis'] }} obat</p>
                <p class="mt-0.5 text-xs text-amber-600 underline">Lihat detail</p>
            </div>
        </a>

        <a href="{{ route('resep.index') }}" wire:navigate class="flex items-start gap-3 rounded-xl border border-sky-200 bg-sky-50 p-4 shadow-sm transition-colors hover:bg-sky-100">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-xs text-sky-700">Resep Menunggu Verifikasi</p>
                <p class="mt-1 text-xl font-semibold text-sky-800">{{ $ringkasan['resep_menunggu_verifikasi'] }}</p>
                <p class="mt-0.5 text-xs text-sky-600 underline">Verifikasi sekarang</p>
            </div>
        </a>
    </div>

    {{-- Kartu sekunder --}}
    <div class="mb-6 grid grid-cols-1 gap-4 {{ auth()->user()->hasRole('owner') ? 'md:grid-cols-2' : '' }}">
        <a href="{{ route('laporan.stok') }}" wire:navigate class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm transition-colors hover:bg-red-100">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-700">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-xs text-red-700">Batch Mendekati Kadaluarsa (30 hari)</p>
                <p class="mt-1 text-xl font-semibold text-red-800">{{ $ringkasan['batch_mendekati_kadaluarsa'] }} batch</p>
                <p class="mt-0.5 text-xs text-red-600 underline">Lihat detail</p>
            </div>
        </a>

        @if (auth()->user()->hasRole('owner'))
            <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <p class="text-xs text-emerald-700">Laba Kotor Bulan Ini</p>
                    <p class="mt-1 text-xl font-semibold text-emerald-800">Rp {{ number_format($ringkasan['keuangan_bulan_ini']['laba_kotor'], 0, ',', '.') }}</p>
                    <p class="mt-0.5 text-xs text-emerald-600">Margin {{ number_format($ringkasan['keuangan_bulan_ini']['margin_persen'], 1) }}%</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Transaksi terbaru --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <span class="text-sm font-semibold text-slate-700">Transaksi Terbaru</span>
            <a href="{{ route('penjualan.riwayat') }}" wire:navigate class="text-xs font-medium text-teal-600 hover:text-teal-700">Lihat semua</a>
        </div>
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-2">No. Transaksi</th>
                    <th class="px-4 py-2">Kasir</th>
                    <th class="px-4 py-2">Waktu</th>
                    <th class="px-4 py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($ringkasan['transaksi_terbaru'] as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 font-mono text-xs text-slate-600">{{ $t->no_transaksi }}</td>
                        <td class="px-4 py-2 text-slate-600">{{ $t->kasir->name }}</td>
                        <td class="px-4 py-2 text-slate-500">{{ $t->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-right font-medium text-slate-800">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">Belum ada transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
