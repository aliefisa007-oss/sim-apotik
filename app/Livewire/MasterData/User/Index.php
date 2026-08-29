<?php

namespace App\Livewire\MasterData\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    /**
     * Urutan role tetap (bukan dari query DB) supaya konsisten dengan
     * urutan di RoleSeeder, terlepas dari urutan insert.
     */
    public const ROLE_ORDER = ['owner', 'admin', 'kasir', 'apoteker', 'gudang'];

    public string $search = '';

    public bool $showModal = false;

    public ?User $editing = null;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', User::class);
        $this->reset(['editing', 'name', 'email', 'password', 'role']);
        $this->is_active = true;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $this->editing = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles->first()?->name ?? '';
        $this->is_active = $user->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->editing?->id),
            ],
            'password' => [$this->editing ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in(self::ROLE_ORDER)],
            'is_active' => ['boolean'],
        ]);

        // Cegah owner menonaktifkan atau mencabut role owner dari akun
        // sendiri (proteksi self-lockout), bukan aturan farmasi/regulasi.
        if ($this->editing && $this->editing->id === auth()->id()) {
            if (!$this->is_active) {
                $this->addError('is_active', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');

                return;
            }

            if ($this->role !== 'owner') {
                $this->addError('role', 'Anda tidak bisa mencabut role owner dari akun Anda sendiri.');

                return;
            }
        }

        if ($this->editing) {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'is_active' => $this->is_active,
            ];

            if ($this->password !== '') {
                $data['password'] = Hash::make($this->password);
            }

            $this->editing->update($data);
            $this->editing->syncRoles([$this->role]);
            session()->flash('success', 'User berhasil diperbarui.');
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'is_active' => $this->is_active,
            ]);
            $user->assignRole($this->role);
            session()->flash('success', 'User berhasil ditambahkan.');
        }

        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.master-data.user.index', [
            'userList' => User::query()
                ->with('roles')
                ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%"))
                ->orderBy('name')
                ->paginate(20),
            'roles' => Role::query()
                ->whereIn('name', self::ROLE_ORDER)
                ->get()
                ->sortBy(fn ($role) => array_search($role->name, self::ROLE_ORDER)),
        ]);
    }
}
