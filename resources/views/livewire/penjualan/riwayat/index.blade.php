<div>
    <x-toast />

    <h1 class="mb-4 text-lg font-semibold text-slate-800">Riwayat Transaksi</h1>

    <div class="mb-3 flex gap-2">
        <input type="date" wire:model.live="dari" class="rounded-md border-slate-300 text-sm">
        <input type="date" wire:model.live="sampai" class="rounded-md border-slate-300 text-sm">
        <select wire:model.live="statusFilter" class="rounded-md border-slate-300 text-sm">
            <option value="">Semua Status</option>
            <option value="selesai">Selesai</option>
            <option value="dibatalkan">Dibatalkan</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">No. Transaksi</th>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2">Kasir</th>
                    <th class="px-3 py-2 text-right">Item</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2">Metode</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($riwayat as $trx)
                    <tr wire:key="trx-{{ $trx->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-700">{{ $trx->no_transaksi }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $trx->created_at->format('d M Y H:i') }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $trx->kasir->name }}</td>
                        <td class="px-3 py-2 text-right text-slate-600">{{ $trx->detail->count() }}</td>
                        <td class="px-3 py-2 text-right font-medium text-slate-800">Rp{{ number_format($trx->total, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 capitalize text-slate-600">{{ $trx->metode_bayar }}</td>
                        <td class="px-3 py-2 text-center">
                            @if ($trx->status === 'selesai')
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Selesai</span>
                            @else
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('penjualan.struk', $trx) }}" target="_blank" class="text-xs font-medium text-slate-600 hover:text-slate-900">Struk</a>
                            @can('void', $trx)
                                @if ($trx->status === 'selesai')
                                    <button wire:click="openVoid({{ $trx->id }})" class="ml-2 text-xs font-medium text-red-600 hover:text-red-800">Batalkan</button>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-10 text-center text-sm text-slate-500">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $riwayat->links() }}</div>

    @if ($showVoidModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/30" wire:click.self="$set('showVoidModal', false)">
            <div class="w-full max-w-sm rounded-md bg-white p-5 shadow-lg">
                <h2 class="mb-2 text-sm font-semibold text-slate-800">Batalkan Transaksi</h2>
                <p class="mb-3 text-xs text-slate-500">Stok akan dikembalikan ke batch asal. Transaksi tetap tersimpan dengan status "Dibatalkan" (tidak dihapus).</p>
                <textarea wire:model="alasanVoid" rows="2" placeholder="Alasan pembatalan" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                @error('alasanVoid') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <div class="mt-3 flex justify-end gap-2">
                    <button wire:click="$set('showVoidModal', false)" class="rounded-md px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100">Batal</button>
                    <button wire:click="confirmVoid" class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700">Ya, Batalkan Transaksi</button>
                </div>
            </div>
        </div>
    @endif
</div>
