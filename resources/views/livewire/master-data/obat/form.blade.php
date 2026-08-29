<div>
    <h1 class="mb-4 text-lg font-semibold text-slate-800">
        {{ $obat ? "Ubah Obat — {$obat->kode_obat}" : 'Tambah Obat' }}
    </h1>

    <form wire:submit="save" class="max-w-3xl space-y-6">

        {{-- Informasi Obat --}}
        <section class="rounded-md border border-slate-200 p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Informasi Obat</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Nama Obat *</label>
                    <input type="text" wire:model="nama_obat" class="w-full rounded-md border-slate-300 text-sm">
                    @error('nama_obat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Nama Generik</label>
                    <input type="text" wire:model="nama_generik" class="w-full rounded-md border-slate-300 text-sm">
                </div>
                <div class="col-span-2">
                    <label class="mb-1 block text-xs font-medium text-slate-600">Deskripsi</label>
                    <textarea wire:model="deskripsi" rows="2" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                </div>
            </div>
        </section>

        {{-- Klasifikasi --}}
        <section class="rounded-md border border-slate-200 p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Klasifikasi</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Kategori *</label>
                    <select wire:model="kategori_id" class="w-full rounded-md border-slate-300 text-sm">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoriOptions as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Golongan *</label>
                    <select wire:model="golongan" class="w-full rounded-md border-slate-300 text-sm">
                        <option value="">-- Pilih Golongan --</option>
                        @foreach (\App\Models\Obat::GOLONGAN_OPTIONS as $g)
                            <option value="{{ $g }}">{{ ucwords(str_replace('_', ' ', $g)) }}</option>
                        @endforeach
                    </select>
                    @error('golongan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="col-span-2 flex items-center gap-2">
                    <input type="checkbox" wire:model="butuh_resep" id="butuh_resep" class="rounded border-slate-300">
                    <label for="butuh_resep" class="text-sm text-slate-700">Membutuhkan resep dokter</label>
                </div>
            </div>
        </section>

        {{-- Kemasan & Satuan --}}
        <section class="rounded-md border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-700">Kemasan & Satuan</h2>
                <button type="button" wire:click="addSatuanRow" class="text-xs font-medium text-slate-600 underline">
                    + Tambah Satuan
                </button>
            </div>

            <p class="mb-2 text-xs text-slate-500">
                Tandai satu satuan sebagai <strong>dasar</strong> (angka konversi = 1). Satuan lain diisi jumlah setara satuan dasar
                — contoh: jika dasar = Tablet, maka Strip = 10, Box = 100.
            </p>

            @error('satuanRows') <p class="mb-2 text-xs text-red-600">{{ $message }}</p> @enderror

            <div class="space-y-2">
                @foreach ($satuanRows as $index => $row)
                    <div wire:key="satuan-row-{{ $index }}" class="grid grid-cols-12 items-center gap-2">
                        <div class="col-span-4">
                            <select wire:model="satuanRows.{{ $index }}.satuan_id" class="w-full rounded-md border-slate-300 text-sm">
                                <option value="">-- Satuan --</option>
                                @foreach ($satuanOptions as $satuan)
                                    <option value="{{ $satuan->id }}">{{ $satuan->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-3">
                            <input
                                type="number" min="1"
                                wire:model="satuanRows.{{ $index }}.konversi_ke_satuan_dasar"
                                {{ $row['is_satuan_dasar'] ? 'disabled value=1' : '' }}
                                class="w-full rounded-md border-slate-300 text-sm"
                                placeholder="Konversi"
                            >
                        </div>
                        <div class="col-span-3 flex items-center gap-1">
                            <input
                                type="radio" name="satuan_dasar"
                                wire:click="setSatuanDasar({{ $index }})"
                                @checked($row['is_satuan_dasar'])
                                class="border-slate-300"
                            >
                            <label class="text-xs text-slate-600">Satuan dasar</label>
                        </div>
                        <div class="col-span-2 text-right">
                            @if (count($satuanRows) > 1)
                                <button type="button" wire:click="removeSatuanRow({{ $index }})" class="text-xs text-red-600">Hapus</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Barcode --}}
        <section class="rounded-md border border-slate-200 p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Barcode</h2>
            <input type="text" wire:model="barcode" placeholder="Kosongkan jika tidak ada" class="w-full max-w-xs rounded-md border-slate-300 text-sm">
            @error('barcode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </section>

        {{-- Status --}}
        <section class="rounded-md border border-slate-200 p-4">
            <h2 class="mb-3 text-sm font-semibold text-slate-700">Status</h2>
            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-slate-300">
                <label for="is_active" class="text-sm text-slate-700">Obat aktif</label>
            </div>
        </section>

        <div class="flex items-center gap-3">
            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Simpan
            </button>
            <a href="{{ route('obat.index') }}" class="text-sm text-slate-500 hover:text-slate-700">Batal</a>
        </div>
    </form>
</div>
