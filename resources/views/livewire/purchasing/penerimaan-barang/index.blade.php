<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Penerimaan Barang</h1>
        <a href="{{ route('penerimaan-barang.create') }}" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
            + Terima Barang (Tanpa PO)
        </a>
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2">PO</th>
                    <th class="px-3 py-2">No. Faktur</th>
                    <th class="px-3 py-2 text-right">Jumlah Item</th>
                    <th class="px-3 py-2">Diterima Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($penerimaanList as $penerimaan)
                    <tr wire:key="penerimaan-{{ $penerimaan->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 text-slate-600">{{ $penerimaan->tanggal_terima->format('d M Y') }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-500">{{ $penerimaan->purchaseOrder->no_po ?? '-' }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $penerimaan->no_faktur_supplier ?? '-' }}</td>
                        <td class="px-3 py-2 text-right text-slate-600">{{ $penerimaan->detail->count() }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $penerimaan->user->name }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-10 text-center text-sm text-slate-500">Belum ada penerimaan barang.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $penerimaanList->links() }}</div>
</div>
