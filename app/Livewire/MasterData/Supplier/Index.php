<?php

namespace App\Livewire\MasterData\Supplier;

use App\Models\Supplier;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?Supplier $editing = null;

    public string $nama_pbf = '';
    public string $no_izin_pbf = '';
    public string $alamat = '';
    public string $kontak = '';
    public bool $is_active = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Supplier::class);
        $this->reset(['editing', 'nama_pbf', 'no_izin_pbf', 'alamat', 'kontak']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $supplier = Supplier::findOrFail($id);
        $this->authorize('update', $supplier);

        $this->editing = $supplier;
        $this->nama_pbf = $supplier->nama_pbf;
        $this->no_izin_pbf = (string) $supplier->no_izin_pbf;
        $this->alamat = (string) $supplier->alamat;
        $this->kontak = (string) $supplier->kontak;
        $this->is_active = $supplier->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama_pbf' => ['required', 'string', 'max:255'],
            'no_izin_pbf' => [
                'nullable', 'string', 'max:100',
                Rule::unique('suppliers', 'no_izin_pbf')->ignore($this->editing?->id),
            ],
            'alamat' => ['nullable', 'string', 'max:500'],
            'kontak' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        $data = [
            'nama_pbf' => $this->nama_pbf,
            'no_izin_pbf' => $this->no_izin_pbf ?: null,
            'alamat' => $this->alamat ?: null,
            'kontak' => $this->kontak ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->editing) {
            $this->editing->update($data);
            session()->flash('success', 'Supplier berhasil diperbarui.');
        } else {
            Supplier::create($data);
            session()->flash('success', 'Supplier berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.master-data.supplier.index', [
            'supplierList' => Supplier::query()
                ->when($this->search, fn ($q) => $q->where('nama_pbf', 'like', "%{$this->search}%"))
                ->orderBy('nama_pbf')
                ->paginate(20),
        ]);
    }
}
