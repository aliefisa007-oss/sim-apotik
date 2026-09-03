<div class="h-[calc(100vh-4rem)] flex gap-4 p-4 bg-slate-50">

    {{-- ============================================================
         KOLOM KIRI: pencarian & hasil produk
         - wire:model.live.debounce.300ms -> tunggu 300ms setelah kasir
           berhenti mengetik sebelum query jalan (bukan tiap keystroke)
         - autofocus + $refresh via keydown biar alur kasir gak perlu klik mouse
    ============================================================ --}}
    <div class="w-3/5 flex flex-col bg-white rounded-xl border border-slate-200 p-4">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            autofocus
            placeholder="Cari nama atau kode obat..."
            class="w-full rounded-lg border-slate-200 focus:border-teal-500 focus:ring-teal-500 text-lg py-3"
        >

        <div class="mt-4 flex-1 overflow-y-auto space-y-1" wire:loading.class="opacity-50">
            @forelse ($this->hasilPencarian as $obat)
                {{-- wire:key WAJIB di setiap item loop Livewire agar diffing
                     DOM akurat saat list berubah (search baru, item hilang, dll),
                     mencegah re-render/flicker yang salah pasang ke row lain --}}
                <button
                    wire:key="obat-{{ $obat->id }}"
                    wire:click="tambahKeCart({{ $obat->id }})"
                    {{-- disable kalau stok habis, biar kasir gak coba tambah item kosong --}}
                    @if(($obat->stok_tersedia ?? 0) <= 0) disabled @endif
                    class="w-full flex items-center justify-between p-3 rounded-lg border border-slate-100
                           hover:bg-teal-50 hover:border-teal-200 transition-colors text-left
                           disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white"
                >
                    <div>
                        <div class="font-medium text-slate-800">{{ $obat->nama }}</div>
                        <div class="text-xs text-slate-400">{{ $obat->kode }} · {{ $obat->satuanDasar->nama ?? '-' }}</div>
                    </div>

                    <div class="text-right">
                        <div class="text-sm font-semibold text-slate-700">
                            Rp {{ number_format($obat->harga_default ?? 0, 0, ',', '.') }}
                        </div>
                        {{-- Badge sisa stok — warna berubah sesuai ambang, biar kasir langsung sadar stok menipis --}}
                        <span @class([
                            'text-xs px-2 py-0.5 rounded-full font-medium',
                            'bg-red-100 text-red-700'    => ($obat->stok_tersedia ?? 0) <= 0,
                            'bg-amber-100 text-amber-700' => ($obat->stok_tersedia ?? 0) > 0 && ($obat->stok_tersedia ?? 0) <= 10,
                            'bg-slate-100 text-slate-600' => ($obat->stok_tersedia ?? 0) > 10,
                        ])>
                            Stok: {{ $obat->stok_tersedia ?? 0 }}
                        </span>
                    </div>
                </button>
            @empty
                <p class="text-slate-400 text-sm text-center py-8">
                    {{ strlen($search) < 2 ? 'Ketik minimal 2 huruf untuk mencari obat' : 'Obat tidak ditemukan' }}
                </p>
            @endforelse
        </div>
    </div>

    {{-- ============================================================
         KOLOM KANAN: keranjang & pembayaran
    ============================================================ --}}
    <div class="w-2/5 flex flex-col bg-white rounded-xl border border-slate-200 p-4">
        <h3 class="font-semibold text-slate-800 mb-3">Keranjang</h3>

        <div class="flex-1 overflow-y-auto space-y-2">
            @forelse ($cart as $obatId => $item)
                <div wire:key="cart-{{ $obatId }}" class="flex items-center justify-between p-2 rounded-lg bg-slate-50">
                    <div>
                        <div class="text-sm font-medium text-slate-800">{{ $item['nama'] }}</div>
                        <div class="text-xs text-slate-400">
                            Batch {{ $item['no_batch'] }} · Exp {{ $item['kadaluarsa'] }}
                        </div>
                        <div class="text-xs text-slate-400">
                            Rp {{ number_format($item['harga'], 0, ',', '.') }} × {{ $item['qty'] }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button wire:click="ubahQty({{ $obatId }}, -1)" class="w-7 h-7 rounded-full border border-slate-200 hover:bg-slate-100">−</button>
                        <span class="w-6 text-center text-sm">{{ $item['qty'] }}</span>
                        <button wire:click="ubahQty({{ $obatId }}, 1)" class="w-7 h-7 rounded-full border border-slate-200 hover:bg-slate-100">+</button>
                        <button wire:click="hapusDariCart({{ $obatId }})" class="ml-1 text-red-400 hover:text-red-600 text-xs">✕</button>
                    </div>
                </div>
            @empty
                <p class="text-slate-400 text-sm text-center py-8">Keranjang kosong</p>
            @endforelse
        </div>

        <div class="border-t border-slate-100 pt-3 mt-3 space-y-2">
            <div class="flex justify-between text-lg font-semibold text-slate-800">
                <span>Total</span>
                <span>Rp {{ number_format($this->totalBelanja, 0, ',', '.') }}</span>
            </div>

            {{-- Tombol jumlah cepat: kasir sering terima nominal bulat.
                 wire:click.prevent supaya gak trigger form submit tak sengaja --}}
            <div class="grid grid-cols-4 gap-2">
                @foreach ([20000, 50000, 100000, 150000] as $nominal)
                    <button
                        wire:click.prevent="$set('jumlahBayar', {{ $nominal }})"
                        class="text-xs py-1.5 rounded-lg border border-slate-200 hover:bg-teal-50 hover:border-teal-300"
                    >
                        {{ number_format($nominal, 0, ',', '.') }}
                    </button>
                @endforeach
            </div>

            <input
                type="number"
                wire:model.live="jumlahBayar"
                placeholder="Jumlah bayar"
                class="w-full rounded-lg border-slate-200 focus:border-teal-500 focus:ring-teal-500"
            >

            <div class="flex justify-between text-sm text-slate-500">
                <span>Kembalian</span>
                <span>Rp {{ number_format($this->kembalian, 0, ',', '.') }}</span>
            </div>

            <button
                wire:click="proses"
                wire:loading.attr="disabled"
                class="w-full py-3 rounded-lg bg-teal-600 text-white font-semibold hover:bg-teal-700
                       disabled:opacity-50 transition-colors"
            >
                <span wire:loading.remove>Proses & Cetak Struk</span>
                <span wire:loading>Memproses...</span>
            </button>
        </div>
    </div>
</div>
