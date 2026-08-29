<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="mb-4 text-lg font-semibold text-slate-800">Penyesuaian Stok (Stok Opname)</h1>

    <form wire:submit="save" class="max-w-xl space-y-4 rounded-md border border-slate-200 p-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Obat *</label>
            <select wire:model.live="obat_id" class="w-full rounded-md border-slate-300 text-sm">
                <option value="">-- Pilih Obat --</option>
                @foreach ($obatOptions as $obat)
                    <option value="{{ $obat->id }}">{{ $obat->kode_obat }} — {{ $obat->nama_obat }}</option>
                @endforeach
            </select>
            @error('obat_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>

        @if ($obat_id)
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Batch *</label>
                <select wire:model.live="batch_id" class="w-full rounded-md border-slate-300 text-sm">
                    <option value="">-- Pilih Batch --</option>
                    @foreach ($batchOptions as $batch)
                        <option value="{{ $batch->id }}">
                            {{ $batch->no_batch }} (stok sistem: {{ $batch->stok_saat_ini }}, exp {{ $batch->tanggal_kadaluarsa->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
                @error('batch_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        @if ($this->batchAktif)
            <div class="rounded-md bg-slate-50 p-3 text-sm">
                <p class="text-slate-600">Stok sistem saat ini: <strong>{{ $this->batchAktif->stok_saat_ini }}</strong></p>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Stok Fisik Hasil Hitung *</label>
                <input type="number" min="0" wire:model.live="stok_fisik_baru" class="w-full rounded-md border-slate-300 text-sm">
                @error('stok_fisik_baru') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            @if ($this->selisih !== null)
                <div class="rounded-md border px-3 py-2 text-sm
                    {{ $this->selisih === 0 ? 'border-slate-200 bg-slate-50 text-slate-600' : ($this->selisih > 0 ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700') }}">
                    Selisih: {{ $this->selisih >= 0 ? '+' : '' }}{{ $this->selisih }}
                    @if ($this->selisih === 0) (tidak ada perubahan) @endif
                </div>
            @endif

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Alasan Penyesuaian *</label>
                <textarea wire:model="alasan" rows="2" placeholder="mis. Hasil stok opname bulan Agustus" class="w-full rounded-md border-slate-300 text-sm"></textarea>
                @error('alasan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Simpan Penyesuaian
            </button>
        @endif
    </form>
</div>
