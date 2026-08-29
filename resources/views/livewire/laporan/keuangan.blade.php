<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Laporan Keuangan</h1>
    <p class="mb-4 text-xs text-slate-500">
        HPP dihitung dari harga beli batch SAAT INI — bisa sedikit bergeser untuk batch yang di-restock dengan harga berbeda setelah sebagian stoknya terjual. Lihat catatan di kode (LaporanKeuanganService).
    </p>

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

    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">Omzet</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['omzet'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-md border border-slate-200 bg-white p-4">
            <p class="text-xs text-slate-500">HPP</p>
            <p class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format($ringkasan['hpp'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-xs text-emerald-700">Laba Kotor</p>
            <p class="mt-1 text-xl font-semibold text-emerald-800">Rp {{ number_format($ringkasan['laba_kotor'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-md border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-xs text-emerald-700">Margin</p>
            <p class="mt-1 text-xl font-semibold text-emerald-800">{{ number_format($ringkasan['margin_persen'], 1) }}%</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <div class="border-b border-slate-200 bg-slate-50 px-3 py-2 text-sm font-semibold text-slate-700">
            Laba Rugi Harian
        </div>
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2 text-right">Omzet</th>
                    <th class="px-3 py-2 text-right">HPP</th>
                    <th class="px-3 py-2 text-right">Laba Kotor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($labaRugiPerHari as $row)
                    <tr>
                        <td class="px-3 py-2 text-slate-600">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-right text-slate-600">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right text-slate-600">Rp {{ number_format($row['hpp'], 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-right font-medium text-emerald-700">Rp {{ number_format($row['laba_kotor'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-6 text-center text-sm text-slate-500">Tidak ada data pada rentang ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
