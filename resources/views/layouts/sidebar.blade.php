@php
    $navGroups = [
        'Master Data' => [
            'active' => ['obat.*', 'kategori-obat.*', 'satuan.*', 'supplier.*', 'pasien.*'],
            'icon' => 'cube',
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
            'icon' => 'archive',
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
            'icon' => 'cash',
            'links' => [
                ['penjualan.kasir', 'Kasir'],
                ['penjualan.riwayat', 'Riwayat Transaksi'],
                ['resep.index', 'Resep'],
            ],
        ],
        'Purchasing' => [
            'active' => ['purchase-order.*', 'penerimaan-barang.*'],
            'icon' => 'truck',
            'links' => [
                ['purchase-order.index', 'Purchase Order'],
                ['penerimaan-barang.index', 'Penerimaan Barang'],
            ],
        ],
        'Laporan' => [
            'active' => ['laporan.*'],
            'icon' => 'chart',
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
            'icon' => 'cog',
            'links' => [
                ['hja-config.edit', 'Konfigurasi HJA'],
                ['pengguna.index', 'Pengguna'],
            ],
        ];
    }

    $icons = [
        'cube' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />',
        'archive' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M4.5 6.75v11.25a1.5 1.5 0 001.5 1.5h12a1.5 1.5 0 001.5-1.5V6.75M9 11.25h6M3 6.75l.94-3.13A1.5 1.5 0 015.38 2.5h13.24a1.5 1.5 0 011.44 1.12l.94 3.13" />',
        'cash' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 8.25v10.5a1.5 1.5 0 001.5 1.5h16.5a1.5 1.5 0 001.5-1.5V8.25M2.25 8.25l1.5-3.75A1.5 1.5 0 015.13 3.5h13.74a1.5 1.5 0 011.38 1l1.5 3.75M12 15a2.25 2.25 0 100-4.5 2.25 2.25 0 000 4.5z" />',
        'truck' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.833H16.5V6a2.25 2.25 0 00-2.25-2.25H3.75A2.25 2.25 0 001.5 6v9.75c0 .621.504 1.125 1.125 1.125H4.5" />',
        'chart' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
        'cog' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.751.43.992l1.005.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.213-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />',
    ];

    $isGroupActive = fn ($group) => request()->routeIs($group['active']);
    $isLinkActive = fn ($route) => request()->routeIs(str_replace('.index', '.*', str_replace('.create', '.*', $route)));
@endphp

{{-- Mobile backdrop --}}
<div
    x-show="sidebarOpen"
    x-transition:enter="transition-opacity ease-linear duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-slate-900/40 lg:hidden"
    style="display: none;"
></div>

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white transition-transform duration-200 ease-in-out lg:translate-x-0"
>
    <div class="flex h-16 shrink-0 items-center gap-2 border-b border-slate-100 px-5">
        <x-application-logo class="h-7 w-auto fill-current text-teal-600" />
        <span class="text-sm font-semibold tracking-tight text-slate-800">SIM Apotik</span>
    </div>

    <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-5">
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-teal-50 text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" />
            </svg>
            Dashboard
        </a>

        @foreach ($navGroups as $label => $group)
            <div x-data="{ open: {{ $isGroupActive($group) ? 'true' : 'false' }} }">
                <button
                    @click="open = ! open"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium transition-colors {{ $isGroupActive($group) ? 'text-teal-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                >
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        {!! $icons[$group['icon']] !!}
                    </svg>
                    <span class="flex-1">{{ $label }}</span>
                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    class="mt-1 space-y-0.5 pl-11"
                    style="display: none;"
                >
                    @foreach ($group['links'] as [$route, $text])
                        <a href="{{ route($route) }}"
                           class="block rounded-md px-3 py-1.5 text-sm transition-colors {{ $isLinkActive($route) ? 'font-medium text-teal-700' : 'text-slate-500 hover:text-slate-900' }}">
                            {{ $text }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    <div class="border-t border-slate-100 p-3">
        <div class="flex items-center gap-2 rounded-lg px-2 py-2">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-600 text-xs font-semibold text-white">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-medium text-slate-700">{{ Auth::user()->name }}</span>
                <span class="block text-xs capitalize text-slate-400">{{ Auth::user()->getRoleNames()->first() ?? '-' }}</span>
            </span>
        </div>
    </div>
</aside>
