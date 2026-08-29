<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">Input Resep Baru</h1>

    <div class="max-w-3xl space-y-4">
        <div class="relative" x-data="{ open: false }" x-on:click.outside="open = false">
            <label class="mb-1 block text-xs font-medium text-slate-600">Pasien *</label>
            <input
                type="text"
                wire:model.live.debounce.200ms="pasienSearch"
                x-on:input="open = true"
                placeholder="Cari nama pasien atau no. RM..."
                class="w-full rounded-md border-slate-300 text-sm"
            >
            @error('pasien_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

            @if (count($pasienResults))
                <div x-show="open" class="absolute z-20 mt-1 w-full rounded-md border border-slate-200 bg-white shadow-lg">
                    @foreach ($pasienResults as $p)
                        <button
                            type="button"
                            wire:click="selectPasien({{ $p['id'] }})"
                            class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-slate-100"
                        >
                            <span>{{ $p['nama_pasien'] }}</span>
                            <span class="text-xs text-slate-400">{{ $p['no_rm'] ?? '-' }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="col-span-2">
                <label class="mb-1 block text-xs font-medium text-slate-600">Nama Dokter *</label>
                <input type="text" wire:model="nama_dokter" class="w-full rounded-md border-slate-300 text-sm">
                @error('nama_dokter') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Tanggal Resep *</label>
                <input type="date" wire:model="tanggal_resep" class="w-full rounded-md border-slate-300 text-sm">
                @error('tanggal_resep') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="col-span-3">
                <label class="mb-1 block text-xs font-medium text-slate-600">No. SIP Dokter</label>
                <input type="text" wire:model="no_sip_dokter" placeholder="Opsional — TO BE VERIFIED apakah wajib" class="w-full rounded-md border-slate-300 text-sm">
            </div>
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-800">Item Obat</h2>
                <button type="button" wire:click="addItem" class="text-xs font-medium text-slate-600 hover:text-slate-900">+ Tambah Baris</button>
            </div>

            <div class="overflow-hidden rounded-md border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Obat</th>
                            <th class="px-3 py-2">Jumlah</th>
                            <th class="px-3 py-2">Aturan Pakai</th>
                            <th class="px-3 py-2">Catatan</th>
                            <th class="px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($items as $i => $item)
                            <tr wire:key="resep-item-{{ $i }}">
                                <td class="px-3 py-2">
                                    <select wire:model="items.{{ $i }}.obat_id" class="w-full rounded-md border-slate-300 text-sm">
                                        <option value="">-- Pilih Obat --</option>
                                        @foreach ($obatOptions as $obat)
                                            <option value="{{ $obat->id }}">{{ $obat->nama_obat }} ({{ $obat->kode_obat }})</option>
                                        @endforeach
                                    </select>
                                    @error("items.{$i}.obat_id") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" min="1" wire:model="items.{{ $i }}.jumlah_diresepkan" class="w-20 rounded-md border-slate-300 text-sm">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model="items.{{ $i }}.aturan_pakai" placeholder="mis. 3x1 sehari" class="w-full rounded-md border-slate-300 text-sm">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model="items.{{ $i }}.catatan" class="w-full rounded-md border-slate-300 text-sm">
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" wire:click="removeItem({{ $i }})" class="text-xs text-red-600">Hapus</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('items') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end gap-2">
            <button type="button" wire:click="save" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Simpan Resep
            </button>
        </div>
    </div>
</div>
