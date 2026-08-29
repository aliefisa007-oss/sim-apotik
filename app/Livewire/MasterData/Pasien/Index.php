<?php

namespace App\Livewire\MasterData\Pasien;

use App\Models\Pasien;
use App\Services\PasienService;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?Pasien $editing = null;

    public string $no_rm = '';
    public string $nama_pasien = '';
    public string $tanggal_lahir = '';
    public string $jenis_kelamin = '';
    public string $alamat = '';
    public string $no_telepon = '';
    public string $alergi = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Pasien::class);
        $this->reset(['editing', 'no_rm', 'nama_pasien', 'tanggal_lahir', 'jenis_kelamin', 'alamat', 'no_telepon', 'alergi']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $pasien = Pasien::findOrFail($id);
        $this->authorize('update', $pasien);

        $this->editing = $pasien;
        $this->no_rm = (string) $pasien->no_rm;
        $this->nama_pasien = $pasien->nama_pasien;
        $this->tanggal_lahir = $pasien->tanggal_lahir?->toDateString() ?? '';
        $this->jenis_kelamin = (string) $pasien->jenis_kelamin;
        $this->alamat = (string) $pasien->alamat;
        $this->no_telepon = (string) $pasien->no_telepon;
        $this->alergi = (string) $pasien->alergi;
        $this->showModal = true;
    }

    public function save(PasienService $service): void
    {
        $this->validate([
            'no_rm' => ['nullable', 'string', 'max:50', Rule::unique('pasien', 'no_rm')->ignore($this->editing?->id)],
            'nama_pasien' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', Rule::in(['L', 'P'])],
            'alamat' => ['nullable', 'string', 'max:500'],
            'no_telepon' => ['nullable', 'string', 'max:30'],
            'alergi' => ['nullable', 'string', 'max:500'],
        ]);

        $data = [
            'no_rm' => $this->no_rm ?: null,
            'nama_pasien' => $this->nama_pasien,
            'tanggal_lahir' => $this->tanggal_lahir ?: null,
            'jenis_kelamin' => $this->jenis_kelamin ?: null,
            'alamat' => $this->alamat ?: null,
            'no_telepon' => $this->no_telepon ?: null,
            'alergi' => $this->alergi ?: null,
        ];

        if ($this->editing) {
            $service->update($this->editing, $data);
            session()->flash('success', 'Data pasien berhasil diperbarui.');
        } else {
            $service->create($data);
            session()->flash('success', 'Pasien berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.master-data.pasien.index', [
            'pasienList' => Pasien::query()
                ->when($this->search, fn ($q) => $q->where('nama_pasien', 'like', "%{$this->search}%")
                    ->orWhere('no_rm', 'like', "%{$this->search}%"))
                ->orderBy('nama_pasien')
                ->paginate(20),
        ]);
    }
}
