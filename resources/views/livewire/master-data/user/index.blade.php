<div>
    @if (session('success'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Pengguna & Role</h1>
        @can('create', \App\Models\User::class)
            <button wire:click="openCreate" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">
                + Tambah Pengguna
            </button>
        @endcan
    </div>

    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau email..."
           class="mb-3 w-72 rounded-md border-slate-300 text-sm">

    <div class="overflow-hidden rounded-md border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Nama</th>
                    <th class="px-3 py-2">Email</th>
                    <th class="px-3 py-2">Role</th>
                    <th class="px-3 py-2 text-center">Status</th>
                    <th class="px-3 py-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($userList as $user)
                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-slate-50">
                        <td class="px-3 py-2 font-medium text-slate-800">
                            {{ $user->name }}
                            @if ($user->id === auth()->id())
                                <span class="ml-1 text-xs text-slate-400">(Anda)</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-slate-600">{{ $user->email }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $user->roles->first()?->name ?? '-' }}</td>
                        <td class="px-3 py-2 text-center">
                            @if ($user->is_active)
                                <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-right">
                            @can('update', $user)
                                <button wire:click="openEdit({{ $user->id }})" class="text-xs font-medium text-slate-600 hover:text-slate-900">Ubah</button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-10 text-center text-sm text-slate-500">
                            Belum ada pengguna selain akun Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $userList->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/30" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-md bg-white p-5 shadow-lg">
                <h2 class="mb-4 text-sm font-semibold text-slate-800">
                    {{ $editing ? 'Ubah Pengguna' : 'Tambah Pengguna' }}
                </h2>
                <form wire:submit="save" class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Nama *</label>
                        <input type="text" wire:model="name" autofocus class="w-full rounded-md border-slate-300 text-sm">
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Email *</label>
                        <input type="email" wire:model="email" class="w-full rounded-md border-slate-300 text-sm">
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">
                            {{ $editing ? 'Password baru (kosongkan jika tidak diubah)' : 'Password *' }}
                        </label>
                        <input type="password" wire:model="password" class="w-full rounded-md border-slate-300 text-sm">
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Role *</label>
                        <select wire:model="role" class="w-full rounded-md border-slate-300 text-sm">
                            <option value="">-- Pilih Role --</option>
                            @foreach ($roles as $roleOption)
                                <option value="{{ $roleOption->name }}">{{ ucfirst($roleOption->name) }}</option>
                            @endforeach
                        </select>
                        @error('role') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="user_is_active" class="rounded border-slate-300">
                        <label for="user_is_active" class="text-sm text-slate-700">Aktif</label>
                        @error('is_active') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-md px-3 py-1.5 text-sm text-slate-500 hover:bg-slate-100">Batal</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="save" wire:loading.class="opacity-60 cursor-wait" class="rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
