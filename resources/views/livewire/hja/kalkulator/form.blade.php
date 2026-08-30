<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="mb-1 text-lg font-semibold text-slate-800">Kalkulator HJA</h1>
    <p class="mb-4 text-sm text-slate-500">
        {{ $batch->obat->nama_obat }} — Batch {{ $batch->no_batch }} (harga beli Rp{{ number_format($batch->harga_beli, 0, ',', '.') }})
    </p>

    <div class="grid max-w-4xl grid-cols-2 gap-6">
        {{-- Input --}}
        <form wire:submit="save" class="space-y-3 rounded-md border border-slate-200 p-4">
            <h2 class="mb-2 text-sm font-semibold text-slate-700">Parameter</h2>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Diskon / Rabat (%)</label>
                <input type="number" step="0.01" wire:model.live="diskon_persen" class="w-full rounded-md border-slate-300 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Pajak (%)</label>
                <input type="number" step="0.01" wire:model.live="tax_percent" class="w-full rounded-md border-slate-300 text-sm">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model.live="harga_termasuk_pajak" id="termasuk_pajak" class="rounded border-slate-300">
                <label for="termasuk_pajak" class="text-sm text-slate-700">Harga faktur sudah termasuk pajak</label>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Metode</label>
                <select wire:model.live="metode" class="w-full rounded-md border-slate-300 text-sm">
                    <option value="markup">Markup</option>
                    <option value="margin">Margin</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">
                    {{ $metode === 'margin' ? 'Margin' : 'Markup' }} (%)
                </label>
                <input type="number" step="0.01" wire:model.live="persen_markup_margin" class="w-full rounded-md border-slate-300 text-sm">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Pembulatan</label>
                    <select wire:model.live="rounding_method" class="w-full rounded-md border-slate-300 text-sm">
                        <option value="round">Round</option>
                        <option value="round_up">Round Up</option>
                        <option value="round_down">Round Down</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Kelipatan (Rp)</label>
                    <input type="number" min="1" wire:model.live="rounding_increment" class="w-full rounded-md border-slate-300 text-sm">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Alasan (opsional)</label>
                <input type="text" wire:model="alasan" placeholder="mis. penyesuaian harga faktur baru" class="w-full rounded-md border-slate-300 text-sm">
            </div>

            @error('preview') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

            <button type="submit" wire:loading.attr="disabled" wire:target="save" wire:loading.class="opacity-60 cursor-wait" class="w-full rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Simpan Harga Jual
            </button>
        </form>

        {{-- Breakdown --}}
        <div class="rounded-md border border-slate-200 p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Breakdown Kalkulasi</h2>

            @if ($this->breakdown)
                @php $b = $this->breakdown; @endphp
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Harga Faktur</dt><dd class="font-mono text-slate-700">Rp{{ number_format($b['harga_faktur'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Diskon ({{ $b['diskon_persen'] }}%)</dt><dd class="font-mono text-slate-500">- Rp{{ number_format($b['harga_faktur'] - $b['harga_netto'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="font-medium text-slate-700">Harga Netto</dt><dd class="font-mono text-slate-800">Rp{{ number_format($b['harga_netto'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Pajak ({{ $b['tax_percent'] }}%{{ $b['harga_termasuk_pajak'] ? ', sudah termasuk' : '' }})</dt><dd class="font-mono text-slate-500">+ Rp{{ number_format($b['pajak_nominal'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="font-medium text-slate-700">Cost Basis</dt><dd class="font-mono text-slate-800">Rp{{ number_format($b['cost_basis'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">{{ ucfirst($b['metode']) }} ({{ $b['persen_markup_margin'] }}%)</dt><dd class="font-mono text-slate-500">Rp{{ number_format($b['harga_sebelum_pembulatan'] - $b['cost_basis'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="text-slate-500">Harga Sebelum Pembulatan</dt><dd class="font-mono text-slate-700">Rp{{ number_format($b['harga_sebelum_pembulatan'], 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Selisih Pembulatan</dt><dd class="font-mono text-slate-500">{{ $b['rounding_difference'] >= 0 ? '+' : '' }}Rp{{ number_format($b['rounding_difference'], 0, ',', '.') }}</dd></div>
                </dl>

                <div class="mt-4 rounded-md bg-slate-800 p-3 text-center">
                    <p class="text-xs text-slate-300">Harga Jual Final</p>
                    <p class="text-2xl font-bold text-white">Rp{{ number_format($b['harga_final'], 0, ',', '.') }}</p>
                </div>
            @else
                <p class="text-sm text-red-600">Parameter tidak valid, lihat pesan error di sisi kiri.</p>
            @endif
        </div>
    </div>
</div>
