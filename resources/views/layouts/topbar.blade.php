<header class="sticky top-0 z-30 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6 lg:px-8">
    <button @click="sidebarOpen = true" type="button" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 lg:hidden">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="min-w-0 flex-1">
        <div class="relative hidden max-w-md sm:block">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
                type="search"
                placeholder="Cari obat, pasien, transaksi..."
                class="w-full rounded-lg border-slate-200 bg-slate-50 py-2 pl-9 text-sm placeholder:text-slate-400 focus:border-teal-500 focus:bg-white focus:ring-teal-500"
                disabled
            >
        </div>
    </div>

    <div class="flex items-center gap-1">
        <button type="button" class="relative rounded-full p-2 text-slate-500 hover:bg-slate-100" title="Notifikasi">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        </button>

        <div x-data="{ open: false }" class="relative" @click.outside="open = false">
            <button @click="open = ! open" type="button" class="flex items-center gap-2 rounded-full p-1 pr-2 hover:bg-slate-100 lg:hidden">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-teal-600 text-xs font-semibold text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg border border-slate-200 bg-white py-1.5 shadow-lg lg:hidden"
                style="display: none;"
            >
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); this.closest('form').submit();"
                       class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                        Log Out
                    </a>
                </form>
            </div>
        </div>

        {{-- Desktop profile dropdown --}}
        <div x-data="{ open: false }" class="relative hidden lg:block" @click.outside="open = false">
            <button @click="open = ! open" type="button" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm hover:bg-slate-50">
                <span class="text-left">
                    <span class="block text-sm font-medium text-slate-700">{{ Auth::user()->name }}</span>
                </span>
                <svg class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-lg border border-slate-200 bg-white py-1.5 shadow-lg"
                style="display: none;"
            >
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); this.closest('form').submit();"
                       class="block px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-slate-900">
                        Log Out
                    </a>
                </form>
            </div>
        </div>
    </div>
</header>
