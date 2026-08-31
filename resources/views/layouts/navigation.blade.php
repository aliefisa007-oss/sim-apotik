<nav x-data="{ mobileOpen: false }" class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex shrink-0 items-center">
                    <x-application-logo class="block h-8 w-auto fill-current text-slate-800" />
                </a>

                <div class="hidden items-center gap-1 lg:flex">
                    <a href="{{ route('dashboard') }}"
                       class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        Dashboard
                    </a>

                    @php
                        $navGroups = [
                            'Master Data' => [
                                'active' => ['obat.*', 'kategori-obat.*', 'satuan.*', 'supplier.*', 'pasien.*'],
                                'links' => [
                                    ['obat.index', 'Obat'],
                                    ['kategori-obat.index', 'Kategori Obat'],
                                    ['satuan.index', 'Satuan'],
                                    ['supplier.index', 'Supplier'],
                                    ['pasien.index', 'Pasien'],
                                ],
                            ],
                            'Inventory' => [
                                'active' => ['batch.*', 'stok-masuk.*', 'penyesuaian-stok.*', 'kartu-stok.*', 'stok-opname.*'],
                                'links' => [
                                    ['batch.index', 'Batch Obat'],
                                    ['stok-masuk.create', 'Stok Masuk'],
                                    ['penyesuaian-stok.create', 'Penyesuaian Stok'],
                                    ['kartu-stok.index', 'Kartu Stok'],
                                    ['stok-opname.index', 'Stok Opname'],
                                ],
                            ],
                            'Penjualan' => [
                                'active' => ['penjualan.*', 'resep.*'],
                                'links' => [
                                    ['penjualan.kasir', 'Kasir'],
                                    ['penjualan.riwayat', 'Riwayat Transaksi'],
                                    ['resep.index', 'Resep'],
                                ],
                            ],
                            'Purchasing' => [
                                'active' => ['purchase-order.*', 'penerimaan-barang.*'],
                                'links' => [
                                    ['purchase-order.index', 'Purchase Order'],
                                    ['penerimaan-barang.index', 'Penerimaan Barang'],
                                ],
                            ],
                            'Laporan' => [
                                'active' => ['laporan.*'],
                                'links' => array_filter([
                                    ['laporan.penjualan', 'Penjualan'],
                                    ['laporan.stok', 'Stok'],
                                    auth()->user()->hasRole('owner') ? ['laporan.keuangan', 'Keuangan'] : null,
                                ]),
                            ],
                        ];

                        if (auth()->user()->hasRole('owner')) {
                            $navGroups['Pengaturan'] = [
                                'active' => ['hja-config.*', 'pengguna.*'],
                                'links' => [
                                    ['hja-config.edit', 'Konfigurasi HJA'],
                                    ['pengguna.index', 'Pengguna'],
                                ],
                            ];
                        }
                    @endphp

                    @foreach ($navGroups as $label => $group)
                        <div x-data="{ open: false }" class="relative" @click.outside="open = false" @keydown.escape.window="open = false">
                            <button
                                @click="open = ! open"
                                type="button"
                                class="flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs($group['active']) ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                            >
                                {{ $label }}
                                <svg class="h-3.5 w-3.5 text-slate-400 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
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
                                class="absolute left-0 z-50 mt-2 w-56 origin-top-left rounded-lg border border-slate-200 bg-white py-1.5 shadow-lg"
                                style="display: none;"
                            >
                                @foreach ($group['links'] as [$route, $text])
                                    <a href="{{ route($route) }}"
                                       class="block px-4 py-2 text-sm transition-colors {{ request()->routeIs(str_replace('.index', '.*', str_replace('.create', '.*', $route))) ? 'bg-slate-50 text-slate-900 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                        {{ $text }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="hidden items-center gap-2 lg:flex">
                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                    <button @click="open = ! open" type="button" class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-slate-50">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-xs font-semibold text-white">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </span>
                        <span class="text-left">
                            <span class="block text-sm font-medium text-slate-700">{{ Auth::user()->name }}</span>
                            <span class="block text-xs text-slate-400">{{ Auth::user()->getRoleNames()->first() ?? '-' }}</span>
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

            <div class="flex items-center lg:hidden">
                <button @click="mobileOpen = ! mobileOpen" type="button" class="rounded-md p-2 text-slate-500 hover:bg-slate-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path x-show="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileOpen" style="display:none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-show="mobileOpen" x-transition style="display: none;" class="border-t border-slate-200 bg-white lg:hidden">
        <div class="space-y-1 px-4 py-3">
            <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-100 text-slate-900' : 'text-slate-600' }}">Dashboard</a>
        </div>

        @foreach ($navGroups as $label => $group)
            <div class="border-t border-slate-100 px-4 py-3">
                <div class="mb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $label }}</div>
                @foreach ($group['links'] as [$route, $text])
                    <a href="{{ route($route) }}" class="block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">{{ $text }}</a>
                @endforeach
            </div>
        @endforeach

        <div class="border-t border-slate-100 px-4 py-3">
            <div class="mb-2 flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-800 text-xs font-semibold text-white">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <div>
                    <div class="text-sm font-medium text-slate-800">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Profile</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); this.closest('form').submit();"
                   class="block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
                    Log Out
                </a>
            </form>
        </div>
    </div>
</nav>