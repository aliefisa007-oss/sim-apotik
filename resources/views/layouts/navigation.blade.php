<nav x-data="{ open: false }" class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <div class="flex shrink-0 items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-slate-800" />
                    </a>
                </div>

                <div class="hidden space-x-6 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-dropdown label="Master Data" :active="request()->routeIs(['obat.*', 'kategori-obat.*', 'satuan.*', 'supplier.*', 'pasien.*'])">
                        <x-dropdown-link :href="route('obat.index')">Obat</x-dropdown-link>
                        <x-dropdown-link :href="route('kategori-obat.index')">Kategori Obat</x-dropdown-link>
                        <x-dropdown-link :href="route('satuan.index')">Satuan</x-dropdown-link>
                        <x-dropdown-link :href="route('supplier.index')">Supplier</x-dropdown-link>
                        <x-dropdown-link :href="route('pasien.index')">Pasien</x-dropdown-link>
                    </x-nav-dropdown>

                    <x-nav-dropdown label="Inventory" :active="request()->routeIs(['batch.*', 'stok-masuk.*', 'penyesuaian-stok.*', 'kartu-stok.*'])">
                        <x-dropdown-link :href="route('batch.index')">Batch Obat</x-dropdown-link>
                        <x-dropdown-link :href="route('stok-masuk.create')">Stok Masuk</x-dropdown-link>
                        <x-dropdown-link :href="route('penyesuaian-stok.create')">Penyesuaian Stok</x-dropdown-link>
                        <x-dropdown-link :href="route('kartu-stok.index')">Kartu Stok</x-dropdown-link>
                    </x-nav-dropdown>

                    <x-nav-dropdown label="Penjualan" :active="request()->routeIs(['penjualan.*', 'resep.*'])">
                        <x-dropdown-link :href="route('penjualan.kasir')">Kasir</x-dropdown-link>
                        <x-dropdown-link :href="route('penjualan.riwayat')">Riwayat Transaksi</x-dropdown-link>
                        <x-dropdown-link :href="route('resep.index')">Resep</x-dropdown-link>
                    </x-nav-dropdown>

                    <x-nav-dropdown label="Purchasing" :active="request()->routeIs(['purchase-order.*', 'penerimaan-barang.*'])">
                        <x-dropdown-link :href="route('purchase-order.index')">Purchase Order</x-dropdown-link>
                        <x-dropdown-link :href="route('penerimaan-barang.index')">Penerimaan Barang</x-dropdown-link>
                    </x-nav-dropdown>

                    <x-nav-dropdown label="Laporan" :active="request()->routeIs('laporan.*')">
                        <x-dropdown-link :href="route('laporan.penjualan')">Penjualan</x-dropdown-link>
                        <x-dropdown-link :href="route('laporan.stok')">Stok</x-dropdown-link>
                        @if (auth()->user()->hasRole('owner'))
                            <x-dropdown-link :href="route('laporan.keuangan')">Keuangan</x-dropdown-link>
                        @endif
                    </x-nav-dropdown>

                    @if (auth()->user()->hasRole('owner'))
                        <x-nav-dropdown label="Pengaturan" :active="request()->routeIs(['hja-config.*', 'pengguna.*'])">
                            <x-dropdown-link :href="route('hja-config.edit')">Konfigurasi HJA</x-dropdown-link>
                            <x-dropdown-link :href="route('pengguna.index')">Pengguna</x-dropdown-link>
                        </x-nav-dropdown>
                    @endif
                </div>
            </div>

            <div class="hidden sm:ms-6 sm:flex sm:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center rounded-md border border-transparent px-3 py-2 text-sm font-medium leading-4 text-slate-500 transition duration-150 ease-in-out hover:text-slate-700 focus:outline-none">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1 text-xs text-slate-400">({{ Auth::user()->getRoleNames()->first() ?? '-' }})</div>
                            <div class="ms-1">
                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-500 focus:bg-slate-100 focus:text-slate-500 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Menu Responsif (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 pb-3 pt-4">
            <div class="px-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Master Data</div>
            <x-responsive-nav-link :href="route('obat.index')" :active="request()->routeIs('obat.*')">Obat</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('kategori-obat.index')" :active="request()->routeIs('kategori-obat.*')">Kategori Obat</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('satuan.index')" :active="request()->routeIs('satuan.*')">Satuan</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('supplier.index')" :active="request()->routeIs('supplier.*')">Supplier</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pasien.index')" :active="request()->routeIs('pasien.*')">Pasien</x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 pb-3 pt-4">
            <div class="px-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Inventory</div>
            <x-responsive-nav-link :href="route('batch.index')" :active="request()->routeIs('batch.*')">Batch Obat</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('stok-masuk.create')" :active="request()->routeIs('stok-masuk.*')">Stok Masuk</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('penyesuaian-stok.create')" :active="request()->routeIs('penyesuaian-stok.*')">Penyesuaian Stok</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('kartu-stok.index')" :active="request()->routeIs('kartu-stok.*')">Kartu Stok</x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 pb-3 pt-4">
            <div class="px-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Penjualan</div>
            <x-responsive-nav-link :href="route('penjualan.kasir')" :active="request()->routeIs('penjualan.kasir')">Kasir</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('penjualan.riwayat')" :active="request()->routeIs('penjualan.riwayat')">Riwayat Transaksi</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('resep.index')" :active="request()->routeIs('resep.*')">Resep</x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 pb-3 pt-4">
            <div class="px-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Purchasing</div>
            <x-responsive-nav-link :href="route('purchase-order.index')" :active="request()->routeIs('purchase-order.*')">Purchase Order</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('penerimaan-barang.index')" :active="request()->routeIs('penerimaan-barang.*')">Penerimaan Barang</x-responsive-nav-link>
        </div>

        <div class="border-t border-slate-200 pb-3 pt-4">
            <div class="px-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Laporan</div>
            <x-responsive-nav-link :href="route('laporan.penjualan')" :active="request()->routeIs('laporan.penjualan')">Penjualan</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('laporan.stok')" :active="request()->routeIs('laporan.stok')">Stok</x-responsive-nav-link>
            @if (auth()->user()->hasRole('owner'))
                <x-responsive-nav-link :href="route('laporan.keuangan')" :active="request()->routeIs('laporan.keuangan')">Keuangan</x-responsive-nav-link>
            @endif
        </div>

        @if (auth()->user()->hasRole('owner'))
            <div class="border-t border-slate-200 pb-3 pt-4">
                <div class="px-4 pb-1 text-xs font-semibold uppercase tracking-wide text-slate-400">Pengaturan</div>
                <x-responsive-nav-link :href="route('hja-config.edit')" :active="request()->routeIs('hja-config.*')">Konfigurasi HJA</x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pengguna.index')" :active="request()->routeIs('pengguna.*')">Pengguna</x-responsive-nav-link>
            </div>
        @endif

        <div class="border-t border-slate-200 pb-1 pt-4">
            <div class="px-4">
                <div class="text-base font-medium text-slate-800">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
