<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="mb-4 text-lg font-semibold text-slate-800">Pengaturan HJA (Default)</h1>
    <p class="mb-4 text-xs text-slate-500">
        Nilai di sini dipakai sebagai default kalkulasi HJA per batch, dan bisa di-override per kalkulasi.
    </p>

    <form wire:submit="save" class="max-w-xl space-y-4 rounded-md border border-slate-200 p-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Pajak Default (%)</label>
                <input type="number" step="0.01" wire:model="default_tax_percent" class="w-full rounded-md border-slate-300 text-sm">
                @error('default_tax_percent') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-center gap-2 pt-5">
                <input type="checkbox" wire:model="harga_beli_termasuk_pajak_default" id="pajak_termasuk" class="rounded border-slate-300">
                <label for="pajak_termasuk" class="text-sm text-slate-700">Harga beli sudah termasuk pajak (default)</label>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Metode Default</label>
                <select wire:model="default_metode" class="w-full rounded-md border-slate-300 text-sm">
                    <option value="markup">Markup</option>
                    <option value="margin">Margin</option>
                </select>
            </div>
            <div></div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Markup Default (%)</label>
                <input type="number" step="0.01" wire:model="default_markup_percent" class="w-full rounded-md border-slate-300 text-sm">
                @error('default_markup_percent') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Margin Default (%)</label>
                <input type="number" step="0.01" wire:model="default_margin_percent" class="w-full rounded-md border-slate-300 text-sm">
                @error('default_margin_percent') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Metode Pembulatan</label>
                <select wire:model="rounding_method" class="w-full rounded-md border-slate-300 text-sm">
                    <option value="round">Round (terdekat)</option>
                    <option value="round_up">Round Up</option>
                    <option value="round_down">Round Down</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Kelipatan Pembulatan (Rp)</label>
                <input type="number" min="1" wire:model="rounding_increment" class="w-full rounded-md border-slate-300 text-sm">
                @error('rounding_increment') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
            Simpan Pengaturan
        </button>
    </form>
</div>
