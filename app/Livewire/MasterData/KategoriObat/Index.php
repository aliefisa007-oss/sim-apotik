<?php

namespace App\Livewire\MasterData\KategoriObat;

use App\Models\KategoriObat;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?KategoriObat $editing = null;

    #[Validate('required|string|max:255')]
    public string $nama = '';

    public ?int $parent_id = null;

    public bool $is_active = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', KategoriObat::class);
        $this->reset(['editing', 'nama', 'parent_id']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $kategori = KategoriObat::findOrFail($id);
        $this->authorize('update', $kategori);

        $this->editing = $kategori;
        $this->nama = $kategori->nama;
        $this->parent_id = $kategori->parent_id;
        $this->is_active = $kategori->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $kategoriId = $this->editing?->id;

        $this->validate([
            'nama' => ['required', 'string', 'max:255'],
            'parent_id' => [
                'nullable',
                'exists:kategori_obat,id',
                $kategoriId ? Rule::notIn([$kategoriId]) : '',
            ],
            'is_active' => ['boolean'],
        ]);

        // Cegah siklus: parent yang dipilih tidak boleh salah satu keturunan sendiri.
        if ($kategoriId && $this->parent_id && $this->isDescendant($kategoriId, $this->parent_id)) {
            $this->addError('parent_id', 'Perubahan ini akan membuat siklus kategori.');
            return;
        }

        $data = [
            'nama' => $this->nama,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editing) {
            $this->editing->update($data);
            session()->flash('success', 'Kategori berhasil diperbarui.');
        } else {
            KategoriObat::create($data);
            session()->flash('success', 'Kategori berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    /** Apakah $candidateParentId adalah keturunan dari $kategoriId? */
    private function isDescendant(int $kategoriId, int $candidateParentId): bool
    {
        $node = KategoriObat::find($candidateParentId);
        $visited = [];

        while ($node) {
            if (in_array($node->id, $visited, true)) {
                break;
            }
            $visited[] = $node->id;

            if ($node->id === $kategoriId) {
                return true;
            }
            $node = $node->parent;
        }

        return false;
    }

    public function render()
    {
        $kategoriList = KategoriObat::query()
            ->with('parent')
            ->when($this->search, fn ($q) => $q->where('nama', 'like', "%{$this->search}%"))
            ->orderBy('nama')
            ->paginate(20);

        return view('livewire.master-data.kategori-obat.index', [
            'kategoriList' => $kategoriList,
            'parentOptions' => KategoriObat::where('is_active', true)
                ->when($this->editing, fn ($q) => $q->where('id', '!=', $this->editing->id))
                ->orderBy('nama')
                ->get(),
        ]);
    }
}
