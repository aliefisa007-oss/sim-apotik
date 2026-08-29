<div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="mb-1 text-lg font-semibold text-slate-800">SIM Apotik</h1>
    <p class="mb-5 text-sm text-slate-500">Masuk untuk melanjutkan</p>

    @if (session('status'))
        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input
                type="email"
                wire:model="email"
                autofocus
                autocomplete="username"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
            <input
                type="password"
                wire:model="password"
                autocomplete="current-password"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" wire:model="remember" class="rounded border-slate-300">
            Ingat saya
        </label>

        <button
            type="submit"
            class="w-full rounded-md bg-slate-800 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700"
            wire:loading.attr="disabled"
        >
            Masuk
        </button>
    </form>
</div>
