<?php

namespace App\Livewire\MasterData\Obat;

use App\Models\KategoriObat;
use App\Models\Obat;
use App\Models\Satuan;
use App\Services\ObatService;
use InvalidArgumentException;
use Livewire\Component;

class Form extends Component
{
    public ?Obat $obat = null;

    public string $nama_obat = '';
    public string $nama_generik = '';
    public ?int $kategori_id = null;
    public string $golongan = '';
    public string $barcode = '';
    public bool $butuh_resep = false;
    public string $deskripsi = '';
    public bool $is_active = true;
    public int $stok_minimum = 10;

    /** @var array<int, array{satuan_id: ?int, konversi_ke_satuan_dasar: int, is_satuan_dasar: bool, is_satuan_jual_default: bool}> */
    public array $satuanRows = [];

    public function mount(?Obat $obat = null): void
    {
        if ($obat && $obat->exists) {
            $this->authorize('update', $obat);

            $this->obat = $obat;
            $this->nama_obat = $obat->nama_obat;
            $this->nama_generik = (string) $obat->nama_generik;
            $this->kategori_id = $obat->kategori_id;
            $this->golongan = $obat->golongan;
            $this->barcode = (string) $obat->barcode;
            $this->butuh_resep = $obat->butuh_resep;
            $this->deskripsi = (string) $obat->deskripsi;
            $this->is_active = $obat->is_active;
            $this->stok_minimum = $obat->stok_minimum;

            $this->satuanRows = $obat->obatSatuan->map(fn ($row) => [
                'satuan_id' => $row->satuan_id,
                'konversi_ke_satuan_dasar' => $row->konversi_ke_satuan_dasar,
                'is_satuan_dasar' => $row->is_satuan_dasar,
                'is_satuan_jual_default' => $row->is_satuan_jual_default,
            ])->toArray();
        } else {
            $this->authorize('create', Obat::class);
            $this->addSatuanRow();
        }
    }

    public function addSatuanRow(): void
    {
        $this->satuanRows[] = [
            'satuan_id' => null,
            'konversi_ke_satuan_dasar' => 1,
            'is_satuan_dasar' => empty($this->satuanRows), // baris pertama default satuan dasar
            'is_satuan_jual_default' => empty($this->satuanRows),
        ];
    }

    public function removeSatuanRow(int $index): void
    {
        unset($this->satuanRows[$index]);
        $this->satuanRows = array_values($this->satuanRows);
    }

    /**
     * Memastikan hanya satu baris yang ditandai sebagai satuan dasar
     * (checkbox berperilaku seperti radio button di UI).
     */
    public function setSatuanDasar(int $index): void
    {
        foreach ($this->satuanRows as $i => $row) {
            $this->satuanRows[$i]['is_satuan_dasar'] = ($i === $index);
        }
    }

    protected function rules(): array
    {
        $obatId = $this->obat?->id;

        return [
            'nama_obat' => ['required', 'string', 'max:255'],
            'nama_generik' => ['nullable', 'string', 'max:255'],
            'kategori_id' => ['required', 'exists:kategori_obat,id'],
            'golongan' => ['required', 'in:' . implode(',', Obat::GOLONGAN_OPTIONS)],
            'barcode' => [
                'nullable', 'string', 'max:64',
                $obatId
                    ? "unique:obat,barcode,{$obatId}"
                    : 'unique:obat,barcode',
            ],
            'butuh_resep' => ['boolean'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'stok_minimum' => ['required', 'integer', 'min:0'],

            'satuanRows' => ['required', 'array', 'min:1'],
            'satuanRows.*.satuan_id' => ['required', 'distinct', 'exists:satuan,id'],
            'satuanRows.*.konversi_ke_satuan_dasar' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function messages(): array
    {
        return [
            'satuanRows.*.satuan_id.distinct' => 'Satuan tidak boleh duplikat.',
        ];
    }

    public function save(ObatService $service): void
    {
        $this->validate();

        $data = [
            'nama_obat' => $this->nama_obat,
            'nama_generik' => $this->nama_generik ?: null,
            'kategori_id' => $this->kategori_id,
            'golongan' => $this->golongan,
            'satuan_dasar_id' => collect($this->satuanRows)->firstWhere('is_satuan_dasar', true)['satuan_id'] ?? null,
            'barcode' => $this->barcode ?: null,
            'butuh_resep' => $this->butuh_resep,
            'deskripsi' => $this->deskripsi ?: null,
            'is_active' => $this->is_active,
            'stok_minimum' => $this->stok_minimum,
        ];

        try {
            if ($this->obat) {
                $service->update($this->obat, $data, $this->satuanRows);
                session()->flash('success', "Obat {$this->obat->kode_obat} berhasil diperbarui.");
            } else {
                $obat = $service->create($data, $this->satuanRows);
                session()->flash('success', "Obat {$obat->kode_obat} berhasil ditambahkan.");
            }
        } catch (InvalidArgumentException $e) {
            $this->addError('satuanRows', $e->getMessage());
            return;
        }

        $this->redirectRoute('obat.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.master-data.obat.form', [
            'kategoriOptions' => KategoriObat::where('is_active', true)->orderBy('nama')->get(),
            'satuanOptions' => Satuan::where('is_active', true)->orderBy('nama')->get(),
        ]);
    }
}
