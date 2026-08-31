<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    @php
        $statusStyles = [
            'berjalan' => 'bg-amber-100 text-amber-700',
            'selesai' => 'bg-emerald-100 text-emerald-700',
            'dibatalkan' => 'bg-slate-100 text-slate-500',
        ];
    @endphp

    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-800">
                Opname {{ $opname->kode_opname }}
                <span class="ml-2 rounded-full px-2 py-0.5 text-xs font-medium {{ $statusStyles[$opname->status] }}">{{ ucfirst($opname->status) }}</span>
            </h1>
            <p class="text-xs text-slate-500">Mulai {{ $opname->tanggal_mulai->format('d/m/Y') }} oleh {{ $opname->pembuat->name }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if ($opname->status === 'selesai')
                <a href="{{ route('stok-opname.cetak', $opname) }}" target="_blank" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cetak Laporan
                </a>
            @endif
            @if ($opname->status === 'berjalan')
                @can('cancel', $opname)
                    <button wire:click="batalkan" wire:confirm="Batalkan sesi opname {{ $opname->kode_opname }}? Semua hasil hitung yang sudah dicatat akan hilang." class="rounded-md border border-red-300 px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">
                        Batalkan
                    </button>
                @endcan
                @can('finalize', $opname)
                    <button
                        wire:click="selesaikan"
                        wire:confirm="Selesaikan opname? Stok riil akan disesuaikan untuk semua item yang selisih dan TIDAK BISA dibatalkan lagi setelah ini."
                        wire:loading.attr="disabled"
                        wire:target="selesaikan"
                        wire:loading.class="opacity-60 cursor-wait"
                        @disabled(!$opname->sudah_lengkap)
                        class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        Selesaikan Opname
                    </button>
                @endcan
            @endif
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-6">
        <div class="rounded-md border border-slate-200 p-3">
            <div class="text-xs text-slate-500">Total Item</div>
            <div class="text-lg font-semibold text-slate-800">{{ $ringkasan['total'] }}</div>
        </div>
        <div class="rounded-md border border-slate-200 p-3">
            <div class="text-xs text-slate-500">Sudah Dihitung</div>
            <div class="text-lg font-semibold text-slate-800">{{ $ringkasan['sudah_dihitung'] }}/{{ $ringkasan['total'] }}</div>
        </div>
        <div class="rounded-md border border-slate-200 p-3">
            <div class="text-xs text-slate-500">Sesuai</div>
            <div class="text-lg font-semibold text-emerald-700">{{ $ringkasan['sesuai'] }}</div>
        </div>
        <div class="rounded-md border border-slate-200 p-3">
            <div class="text-xs text-slate-500">Lebih</div>
            <div class="text-lg font-semibold text-sky-700">{{ $ringkasan['lebih'] }}</div>
        </div>
        <div class="rounded-md border border-slate-200 p-3">
            <div class="text-xs text-slate-500">Kurang</div>
            <div class="text-lg font-semibold text-red-700">{{ $ringkasan['kurang'] }}</div>
        </div>
        <div class="rounded-md border border-slate-200 p-3">
            <div class="text-xs text-slate-500">Nilai Selisih</div>
            <div class="text-lg font-semibold {{ $ringkasan['nilai_selisih'] < 0 ? 'text-red-700' : 'text-slate-800' }}">
                Rp{{ number_format($ringkasan['nilai_selisih'], 0, ',', '.') }}
            </div>
        </div>
    </div>

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari obat / no batch..."
           class="mb-3 w-72 rounded-md border-slate-300 text-sm">

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Obat</th>
                    <th class="px-3 py-2">No. Batch</th>
                    <th class="px-3 py-2 text-right">Stok Sistem</th>
                    <th class="px-3 py-2 text-right">Stok Fisik</th>
                    <th class="px-3 py-2 text-right">Selisih</th>
                    <th class="px-3 py-2">Catatan</th>
                    <th class="px-3 py-2">Dihitung Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($detailList as $detail)
                    <tr wire:key="detail-{{ $detail->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 text-slate-800">{{ $detail->batchObat->obat->nama_obat }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $detail->batchObat->no_batch }}</td>
                        <td class="px-3 py-2 text-right text-slate-600">{{ $detail->stok_sistem }}</td>
                        <td class="px-3 py-2 text-right">
                            @if ($opname->status === 'berjalan')
                                <input
                                    type="number"
                                    min="0"
                                    wire:model="stokFisikInput.{{ $detail->id }}"
                                    wire:change="simpanHitung({{ $detail->id }})"
                                    class="w-20 rounded-md border-slate-300 py-1 text-right text-sm"
                                >
                                @error("stokFisikInput.{$detail->id}") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            @else
                                {{ $detail->stok_fisik ?? '-' }}
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right font-medium {{ $detail->selisih === null ? 'text-slate-400' : ($detail->selisih === 0 ? 'text-emerald-700' : ($detail->selisih > 0 ? 'text-sky-700' : 'text-red-700')) }}">
                            {{ $detail->selisih === null ? '-' : ($detail->selisih > 0 ? '+' . $detail->selisih : $detail->selisih) }}
                        </td>
                        <td class="px-3 py-2">
                            @if ($opname->status === 'berjalan')
                                <input
                                    type="text"
                                    wire:model="catatanInput.{{ $detail->id }}"
                                    wire:change="simpanHitung({{ $detail->id }})"
                                    placeholder="opsional"
                                    class="w-40 rounded-md border-slate-300 py-1 text-xs"
                                >
                            @else
                                <span class="text-xs text-slate-500">{{ $detail->catatan ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-500">{{ $detail->penghitung?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">Tidak ada item yang cocok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $detailList->links() }}</div>
</div>
