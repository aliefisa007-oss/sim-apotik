<div
    x-data="{ highlightedIndex: 0 }"
    x-on:keydown.window.ctrl.enter.prevent="$wire.openPayment()"
    x-on:keydown.window.f10.prevent="$wire.openPayment()"
>
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Kasir</h1>
        @if (count($cart))
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">{{ count($cart) }} item di keranjang</span>
        @endif
    </div>

    @error('cart') <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div> @enderror
    @error('checkout') <div class="mb-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</div> @enderror

    {{-- Resep terpilih --}}
    <div class="mb-4" x-data="{ open: false }" x-on:click.outside="open = false">
        @if ($resepId)
            <div class="flex items-center justify-between rounded-lg border border-sky-200 bg-sky-50 px-4 py-2.5 text-sm text-sky-800">
                <span>Resep <strong>{{ $resepNoTampil }}</strong> — pasien <strong>{{ $resepPasienTampil }}</strong> (keranjang terisi otomatis, approval apoteker mengikuti verifikasi resep)</span>
                <button type="button" wire:click="clearResep" class="text-xs font-medium text-sky-600 underline hover:text-sky-800">Lepas resep</button>
            </div>
        @else
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.200ms="resepSearch"
                    x-on:input="open = true"
                    placeholder="Cari resep terverifikasi (no. resep / nama pasien)..."
                    class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-slate-400 focus:ring-slate-400"
                >
                @if (count($resepResults))
                    <div x-show="open" x-transition class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg" style="display: none;">
                        @foreach ($resepResults as $r)
                            <button
                                type="button"
                                wire:click="selectResep({{ $r['id'] }})"
                                class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm hover:bg-slate-50"
                            >
                                <span>{{ $r['no_resep'] }} — {{ $r['pasien_nama'] }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- Kolom kiri: search + cart --}}
        <div class="space-y-3 lg:col-span-2">
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
                    class="w-full rounded-lg border-slate-300 text-lg shadow-sm focus:border-slate-400 focus:ring-slate-400"
                >

                @if (count($searchResults))
                    <div x-show="open" x-transition class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg" style="display: none;">
                        @foreach ($searchResults as $i => $result)
                            <button
                                type="button"
                                x-ref="result-{{ $i }}"
                                data-obat-id="{{ $result['id'] }}"
                                wire:click="addToCart({{ $result['id'] }})"
                                :class="highlightedIndex === {{ $i }} ? 'bg-slate-100' : ''"
                                class="flex w-full items-center justify-between px-4 py-2.5 text-left text-sm hover:bg-slate-50"
                            >
                                <span>{{ $result['nama_obat'] }} <span class="text-xs text-slate-400">({{ $result['kode_obat'] }})</span></span>
                                <x-badge-golongan :golongan="$result['golongan']" />
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Obat</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-right">Estimasi Harga</th>
                            <th class="px-4 py-3 text-right">Subtotal</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($cart as $item)
                            <tr wire:key="cart-{{ $item['obat_id'] }}" class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-800">
                                    {{ $item['nama_obat'] }}
                                    @if (in_array($item['golongan'], ['keras', 'narkotika', 'psikotropika']))
                                        <x-badge-golongan :golongan="$item['golongan']" />
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <input
                                        type="number" min="1"
                                        value="{{ $item['jumlah'] }}"
                                        wire:change="updateJumlah({{ $item['obat_id'] }}, $event.target.value)"
                                        class="w-16 rounded-md border-slate-300 text-right text-sm"
                                    >
                                </td>
                                <td class="px-4 py-3 text-right text-slate-500">Rp{{ number_format($item['harga_estimasi'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-slate-800">Rp{{ number_format($item['jumlah'] * $item['harga_estimasi'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" wire:click="removeFromCart({{ $item['obat_id'] }})" class="text-xs font-medium text-red-600 hover:text-red-800">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-14 text-center text-sm text-slate-400">Keranjang kosong. Cari obat untuk mulai transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Kolom kanan: ringkasan --}}
        <div class="lg:sticky lg:top-20 lg:self-start">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Total Estimasi</span>
                    <span class="font-medium text-slate-700">Rp{{ number_format($this->totalEstimasi, 0, ',', '.') }}</span>
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
                    class="mt-4 w-full rounded-lg bg-slate-800 px-4 py-3 text-sm font-medium text-white shadow-sm transition-colors hover:bg-slate-700"
                >
                    Bayar <span class="text-slate-400">(Ctrl+Enter / F10)</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal pembayaran — tanpa animasi entrance JS, biar tidak konflik
         dengan proses morph Livewire tiap kali field di dalamnya berubah. --}}
    @if ($showPaymentModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/40 px-4 backdrop-blur-sm">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-800">Pembayaran</h2>
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="mb-4 flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3">
                    <span class="text-sm font-medium text-slate-600">Total</span>
                    <span class="text-xl font-semibold text-slate-800">Rp{{ number_format($this->totalEstimasi, 0, ',', '.') }}</span>
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
                        <input type="number" step="0.01" wire:model.live.debounce.400ms="jumlahBayar" autofocus class="w-full rounded-md border-slate-300 text-sm">
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

                <div class="flex justify-end gap-2 pt-3">
                    <button type="button" wire:click="$set('showPaymentModal', false)" class="rounded-md px-4 py-2 text-sm text-slate-500 hover:bg-slate-100">Batal</button>
                    <button type="button" wire:click="checkout" wire:loading.attr="disabled" wire:target="checkout" wire:loading.class="opacity-60 cursor-wait" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-700">
                        Selesaikan Transaksi
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>