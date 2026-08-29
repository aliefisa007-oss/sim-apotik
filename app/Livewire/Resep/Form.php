<?php

namespace App\Livewire\Resep;

use App\Models\Obat;
use App\Models\Pasien;
use App\Models\Resep;
use App\Services\ResepService;
use Livewire\Component;

class Form extends Component
{
    public ?int $pasien_id = null;
    public string $nama_dokter = '';
    public string $no_sip_dokter = '';
    public string $tanggal_resep = '';

    /** @var array<int, array{obat_id: ?int, jumlah_diresepkan: ?int, aturan_pakai: string, catatan: string}> */
    public array $items = [];

    public string $pasienSearch = '';

    /** @var array<int, array{id:int,nama_pasien:string,no_rm:?string}> */
    public array $pasienResults = [];

    public function mount(): void
    {
        $this->authorize('create', Resep::class);
        $this->tanggal_resep = now()->toDateString();
        $this->addItem();
    }

    public function updatedPasienSearch(): void
    {
        if (strlen($this->pasienSearch) < 2) {
            $this->pasienResults = [];
            return;
        }

        $this->pasienResults = Pasien::where('is_active', true)
            ->where(function ($q) {
                $q->where('nama_pasien', 'like', "%{$this->pasienSearch}%")
                    ->orWhere('no_rm', 'like', "%{$this->pasienSearch}%");
            })
            ->limit(8)
            ->get(['id', 'nama_pasien', 'no_rm'])
            ->toArray();
    }

    public function selectPasien(int $pasienId): void
    {
        $this->pasien_id = $pasienId;
        $this->pasienSearch = Pasien::find($pasienId)?->nama_pasien ?? '';
        $this->pasienResults = [];
    }

    public function addItem(): void
    {
        $this->items[] = ['obat_id' => null, 'jumlah_diresepkan' => null, 'aturan_pakai' => '', 'catatan' => ''];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    protected function rules(): array
    {
        return [
            'pasien_id' => ['required', 'exists:pasien,id'],
            'nama_dokter' => ['required', 'string', 'max:255'],
            'no_sip_dokter' => ['nullable', 'string', 'max:100'],
            'tanggal_resep' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.obat_id' => ['required', 'exists:obat,id'],
            'items.*.jumlah_diresepkan' => ['required', 'integer', 'min:1'],
            'items.*.aturan_pakai' => ['nullable', 'string', 'max:255'],
            'items.*.catatan' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function save(ResepService $service): void
    {
        $this->validate();

        $resep = $service->create(
            data: [
                'pasien_id' => $this->pasien_id,
                'nama_dokter' => $this->nama_dokter,
                'no_sip_dokter' => $this->no_sip_dokter ?: null,
                'tanggal_resep' => $this->tanggal_resep,
            ],
            detailList: array_map(fn ($item) => [
                'obat_id' => $item['obat_id'],
                'jumlah_diresepkan' => $item['jumlah_diresepkan'],
                'aturan_pakai' => $item['aturan_pakai'] ?: null,
                'catatan' => $item['catatan'] ?: null,
            ], $this->items),
            createdBy: auth()->id(),
        );

        session()->flash('success', "Resep {$resep->no_resep} berhasil dibuat, menunggu verifikasi apoteker.");
        $this->redirectRoute('resep.index', navigate: true);
    }

    public function render()
    {
        return view('livewire.resep.form', [
            'obatOptions' => Obat::where('is_active', true)->orderBy('nama_obat')->get(),
        ]);
    }
}
