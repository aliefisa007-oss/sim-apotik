<?php

namespace App\Livewire\MasterData\Satuan;

use App\Models\Satuan;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?Satuan $editing = null;

    public string $nama = '';

    public bool $is_active = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Satuan::class);
        $this->reset(['editing', 'nama']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $satuan = Satuan::findOrFail($id);
        $this->authorize('update', $satuan);

        $this->editing = $satuan;
        $this->nama = $satuan->nama;
        $this->is_active = $satuan->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama' => [
                'required', 'string', 'max:100',
                Rule::unique('satuan', 'nama')->ignore($this->editing?->id),
            ],
            'is_active' => ['boolean'],
        ]);

        if ($this->editing) {
            $this->editing->update(['nama' => $this->nama, 'is_active' => $this->is_active]);
            session()->flash('success', 'Satuan berhasil diperbarui.');
        } else {
            Satuan::create(['nama' => $this->nama, 'is_active' => $this->is_active]);
            session()->flash('success', 'Satuan berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.master-data.satuan.index', [
            'satuanList' => Satuan::query()
                ->when($this->search, fn ($q) => $q->where('nama', 'like', "%{$this->search}%"))
                ->orderBy('nama')
                ->paginate(20),
        ]);
    }
}
