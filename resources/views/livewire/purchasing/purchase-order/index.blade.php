<div>
    <x-toast />

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Purchase Order</h1>
        @can('create', \App\Models\PurchaseOrder::class)
            <a href="{{ route('purchase-order.create') }}" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                + Buat PO
            </a>
        @endcan
    </div>

    <select wire:model.live="statusFilter" class="mb-3 rounded-md border-slate-300 text-sm">
        <option value="">Semua Status</option>
        <option value="draft">Draft</option>
        <option value="dikirim">Dikirim</option>
        <option value="diterima_sebagian">Diterima Sebagian</option>
        <option value="selesai">Selesai</option>
        <option value="batal">Batal</option>
    </select>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">No. PO</th>
                    <th class="px-3 py-2">Supplier</th>
                    <th class="px-3 py-2">Tanggal</th>
                    <th class="px-3 py-2 text-right">Total</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($poList as $po)
                    @php
                        $statusStyles = [
                            'draft' => 'bg-slate-100 text-slate-600',
                            'dikirim' => 'bg-sky-100 text-sky-700',
                            'diterima_sebagian' => 'bg-amber-100 text-amber-700',
                            'selesai' => 'bg-emerald-100 text-emerald-700',
                            'batal' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <tr wire:key="po-{{ $po->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-mono text-xs text-slate-700">{{ $po->no_po }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $po->supplier->nama_pbf }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $po->tanggal_po->format('d M Y') }}</td>
                        <td class="px-3 py-2 text-right font-medium text-slate-800">Rp{{ number_format($po->total, 0, ',', '.') }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusStyles[$po->status] }}">
                                {{ ucwords(str_replace('_', ' ', $po->status)) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if ($po->status === 'draft')
                                <button wire:click="kirim({{ $po->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-900">Kirim</button>
                                <button wire:click="batalkan({{ $po->id }})" wire:confirm="Batalkan PO {{ $po->no_po }}?" class="ml-2 text-xs font-medium text-red-600 hover:text-red-800">Batalkan</button>
                            @endif
                            @if (in_array($po->status, ['dikirim', 'diterima_sebagian']))
                                <a href="{{ route('penerimaan-barang.create', ['po_id' => $po->id]) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">Terima Barang</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-10 text-center text-sm text-slate-500">
                            Belum ada Purchase Order.
                            @can('create', \App\Models\PurchaseOrder::class)
                                <a href="{{ route('purchase-order.create') }}" class="text-slate-700 underline">+ Buat PO pertama</a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $poList->links() }}</div>
</div>
