{{--
    Toast notifikasi terpusat, dibaca dari session('success') / session('error').
    Taruh <x-toast /> di baris pertama tiap Livewire view yang butuh flash message.
    Auto-dismiss 5 detik, bisa ditutup manual, transisi masuk/keluar via Alpine.
--}}
@if (session('success') || session('error'))
    <div class="pointer-events-none fixed inset-x-4 top-4 z-[100] flex flex-col items-end gap-2 sm:inset-x-auto sm:right-4">
        @if (session('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 5000)"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-[-8px] sm:translate-x-4 sm:translate-y-0"
                x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-3 shadow-lg"
            >
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="flex-1 text-sm text-emerald-800">{{ session('success') }}</p>
                <button @click="show = false" type="button" class="shrink-0 text-emerald-500 hover:text-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 7000)"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-[-8px] sm:translate-x-4 sm:translate-y-0"
                x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border border-red-200 bg-red-50 p-3 shadow-lg"
            >
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <p class="flex-1 text-sm text-red-800">{{ session('error') }}</p>
                <button @click="show = false" type="button" class="shrink-0 text-red-500 hover:text-red-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif
    </div>
@endif
