<div
    x-data="{ highlightedIndex: 0 }"
    x-on:keydown.window.ctrl.enter.prevent="$wire.openPayment()"
    x-on:keydown.window.f10.prevent="$wire.openPayment()"
>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Kasir</h1>

    @error('cart') <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div> @enderror
    @error('checkout') <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div> @enderror

    {{-- Resep terpilih (Phase 6) --}}
    <div class="mb-3" x-data="{ open: false }" x-on:click.outside="open = false">
        @if ($resepId)
            <div class="flex items-center justify-between rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-800">
                <span>Resep <strong>{{ $resepNoTampil }}</strong> — pasien <strong>{{ $resepPasienTampil }}</strong> (keranjang terisi otomatis dari resep, approval apoteker mengikuti verifikasi resep)</span>
                <button type="button" wire:click="clearResep" class="text-xs text-sky-600 underline">Lepas resep</button>
            </div>
        @else
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.200ms="resepSearch"
                    x-on:input="open = true"
                    placeholder="Cari resep terverifikasi (no. resep / nama pasien)..."
                    class="w-full rounded-md border-slate-300 text-sm"
                >
                @if (count($resepResults))
                    <div x-show="open" class="absolute z-20 mt-1 w-full rounded-md border border-slate-200 bg-white shadow-lg">
                        @foreach ($resepResults as $r)
                            <button
                                type="button"
                                wire:click="selectResep({{ $r['id'] }})"
                                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-slate-100"
                            >
                                <span>{{ $r['no_resep'] }} — {{ $r['pasien_nama'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-3 gap-4">
        {{-- Kolom kiri: search + cart --}}
        <div class="col-span-2 space-y-3">
            <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
                <input
                    type="text"
                    wire:model.live.debounce.200ms="search"
                    x-on:input="open = true"
                    x-on:keydown.arrow-down.prevent="highlightedIndex = Math.min(highlightedIndex + 1, {{ max(count($searchResults) - 1, 0) }})"
                    x-on:keydown.arrow-up.prevent="highlightedIndex = Math.max(highlightedIndex - 1, 0)"
                    x-on:keydown.enter.prevent="$wire.addToCart($refs['result-' + highlightedIndex]?.dataset.obatId)"
                    autofocus
                    placeholder="Cari obat (nama / generik) atau scan barcode..."
                    class="w-full rounded-md border-slate-300 text-lg"
                >

                @if (count($searchResults))
                    <div x-show="open" class="absolute z-20 mt-1 w-full rounded-md border border-slate-200 bg-white shadow-lg">
                        @foreach ($searchResults as $i => $result)
                            <button
                                type="button"
                                x-ref="result-{{ $i }}"
                                data-obat-id="{{ $result['id'] }}"
                                wire:click="addToCart({{ $result['id'] }})"
                                :class="highlightedIndex === {{ $i }} ? 'bg-slate-100' : ''"
                                class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-slate-100"
                            >
                                <span>{{ $result['nama_obat'] }} <span class="text-xs text-slate-400">({{ $result['kode_obat'] }})</span></span>
                                <x-badge-golongan :golongan="$result['golongan']" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-md border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Obat</th>
                            <th class="px-3 py-2 text-right">Jumlah</th>
                            <th class="px-3 py-2 text-right">Estimasi Harga</th>
                            <th class="px-3 py-2 text-right">Subtotal</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($cart as $item)
                            <tr wire:key="cart-{{ $item['obat_id'] }}">
                                <td class="px-3 py-2 text-slate-800">
                                    {{ $item['nama_obat'] }}
                                    @if (in_array($item['golongan'], ['keras', 'narkotika', 'psikotropika']))
                                        <x-badge-golongan :golongan="$item['golongan']" />
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <input
                                        type="number" min="1"
                                        value="{{ $item['jumlah'] }}"
                                        wire:change="updateJumlah({{ $item['obat_id'] }}, $event.target.value)"
                                        class="w-16 rounded-md border-slate-300 text-right text-sm"
                                    >
                                </td>
                                <td class="px-3 py-2 text-right text-slate-500">Rp{{ number_format($item['harga_estimasi'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right font-medium text-slate-800">Rp{{ number_format($item['jumlah'] * $item['harga_estimasi'], 0, ',', '.') }}</td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" wire:click="removeFromCart({{ $item['obat_id'] }})" class="text-xs text-red-600">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-10 text-center text-sm text-slate-500">Keranjang kosong. Cari obat untuk mulai transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kolom kanan: ringkasan --}}
        <div class="space-y-3">
            <div class="rounded-md border border-slate-200 p-4">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Total Estimasi</span>
                    <span>Rp{{ number_format($this->totalEstimasi, 0, ',', '.') }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">Harga final dihitung ulang saat checkout berdasarkan batch aktual.</p>

                @if ($this->butuhApproval)
                    <div class="mt-3 rounded-md bg-amber-50 px-3 py-2 text-xs text-amber-700">
                        Transaksi ini memuat obat golongan keras/narkotika/psikotropika — approval apoteker wajib diisi saat checkout.
                    </div>
                @endif

                <button
                    type="button"
                    wire:click="openPayment"
                    class="mt-4 w-full rounded-md bg-slate-800 px-4 py-3 text-sm font-medium text-white hover:bg-slate-700"
                >
                    Bayar (Ctrl+Enter / F10)
                </button>
            </div>
        </div>
    </div>

    {{-- Modal pembayaran --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/30">
            <div class="w-full max-w-md rounded-md bg-white p-5 shadow-lg">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">Pembayaran</h2>

                <div class="mb-3 flex justify-between text-lg font-semibold text-slate-800">
                    <span>Total</span>
                    <span>Rp{{ number_format($this->totalEstimasi, 0, ',', '.') }}</span>
                </div>

                <div class="mb-3">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Metode Bayar</label>
                    <select wire:model.live="metodeBayar" class="w-full rounded-md border-slate-300 text-sm">
                        <option value="tunai">Tunai</option>
                        <option value="debit">Kartu Debit</option>
                        <option value="kredit">Kartu Kredit</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                    </select>
                </div>

                @if ($metodeBayar === 'tunai')
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Jumlah Bayar</label>
                        <input type="number" step="0.01" wire:model.live="jumlahBayar" autofocus class="w-full rounded-md border-slate-300 text-sm">
                        @if ($this->kembalianEstimasi !== null)
                            <p class="mt-1 text-xs text-slate-500">Kembalian (estimasi): Rp{{ number_format($this->kembalianEstimasi, 0, ',', '.') }}</p>
                        @endif
                    </div>
                @endif

                @if ($this->butuhApproval)
                    @if ($resepId)
                        <div class="mb-3 rounded-md bg-sky-50 px-3 py-2 text-xs text-sky-700">
                            Approval apoteker otomatis mengikuti verifikasi resep {{ $resepNoTampil }}.
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="mb-1 block text-xs font-medium text-slate-600">Apoteker Penanggung Jawab *</label>
                            <select wire:model="apotekerApprovalId" class="w-full rounded-md border-slate-300 text-sm">
                                <option value="">-- Pilih Apoteker --</option>
                                @foreach ($apotekerOptions as $apoteker)
                                    <option value="{{ $apoteker->id }}">{{ $apoteker->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="rounded-md px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100">Batal</button>
                    <button type="button" wire:click="checkout" wire:loading.attr="disabled" wire:target="checkout" wire:loading.class="opacity-60 cursor-wait" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        Selesaikan Transaksi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
