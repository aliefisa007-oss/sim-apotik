<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Stok Opname</h1>
        @can('create', \App\Models\StokOpname::class)
            @if (!$adaSesiBerjalan)
                <button wire:click="mulai" wire:loading.attr="disabled" wire:target="mulai" wire:loading.class="opacity-60 cursor-wait" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                    + Mulai Opname Bulan Ini
                </button>
            @else
                <span class="text-xs text-slate-500">Masih ada sesi berjalan — selesaikan/batalkan dulu sebelum mulai sesi baru.</span>
            @endif
        @endcan
    </div>

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Kode</th>
                    <th class="px-3 py-2">Tanggal Mulai</th>
                    <th class="px-3 py-2">Dibuat Oleh</th>
                    <th class="px-3 py-2 text-center">Item</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($opnameList as $opname)
                    @php
                        $statusStyles = [
                            'berjalan' => 'bg-amber-100 text-amber-700',
                            'selesai' => 'bg-emerald-100 text-emerald-700',
                            'dibatalkan' => 'bg-slate-100 text-slate-500',
                        ];
                    @endphp
                    <tr wire:key="opname-{{ $opname->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-medium text-slate-800">{{ $opname->kode_opname }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $opname->tanggal_mulai->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $opname->pembuat->name }}</td>
                        <td class="px-3 py-2 text-center text-slate-600">{{ $opname->detail_count }}</td>
                        <td class="px-3 py-2 text-center">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusStyles[$opname->status] }}">
                                {{ ucfirst($opname->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            <a href="{{ route('stok-opname.show', $opname) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900">
                                {{ $opname->status === 'berjalan' ? 'Lanjutkan' : 'Lihat' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-10 text-center text-sm text-slate-500">
                            Belum ada sesi opname.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $opnameList->links() }}</div>
</div>
