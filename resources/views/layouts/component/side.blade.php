@php
    $menu = [
        (object) [
            'title' => 'Dasboard',
            'path' => '/',
            'icon' => 'fas fa-home fa-fw',
        ],
        (object) [
            'title' => 'Transaksi',
            'icon' => 'fas fa-exchange-alt fa-fw',
            'children' => [
                (object) [
                    'title' => 'Transaksi Masuk',
                    'path' => 'tr_masuk',
                ],
                (object) [
                    'title' => 'Transaksi Pakai',
                    'path' => 'tr_pakai',
                ],
            ],
        ],
        (object) [
            'title' => 'Report / Laporan',
            'icon' => 'fas fa-book fa-fw',
            'children' => [
                (object) [
                    'title' => 'Report',
                    'path' => 'report',
                    'icon' => 'fas fa-book fa-fw',
                ],
                (object) [
                    'title' => 'Report Kategori',
                    'path' => 'report-category',
                    'icon' => 'fas fa-book fa-fw',
                ],
                (object) [
                    'title' => 'Report Stok Akhir',
                    'path' => 'report_stok',
                    'icon' => 'fas fa-book fa-fw',
                ],
            ],
        ],
        (object) [
            'title' => 'Setting',
            'icon' => 'fas fa-bars fa-fw',
            'children' => [
                (object) [
                    'title' => 'Kategori Karung',
                    'path' => 'categories',
                    'icon' => 'fas fa-bars fa-fw',
                ],
                (object) [
                    'title' => 'Mesin',
                    'path' => 'mesin',
                    'icon' => 'fas fa-city fa-fw',
                ],
                (object) [
                    'title' => 'User',
                    'path' => 'users',
                    'icon' => 'fas fa-user fa-fw',
                ],
                (object) [
                    'title' => 'Isi Perball',
                    'path' => 'isi_perball',
                    'icon' => 'fas fa-barcode fa-fw',
                ],
            ],
        ],
    ];
@endphp

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link d-flex align-items-center">
        <img src="{{ asset('img/logo.jpg') }}" alt="Logo" class="brand-image img-circle elevation-3 animate-spin">
        <span class="brand-text text-white ms-2 animate-fade">KARTOCK</span>
    </a>

    <div class="sidebar">
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('storage/images/' . Auth::user()->photo) }}" class="img-circle elevation-2"
                    alt="User Photo" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px; ">
            </div>
            <div class="info">
                <h3 class="d-block text-white">{{ Auth::user()->name }}</h3>
            </div>
        </div>

        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                @foreach ($menu as $item)
                    @if (isset($item->children))
                        <!-- Menu dengan Submenu -->
                        <li
                            class="nav-item {{ collect($item->children)->contains(fn($child) => request()->is($child->path)) ? 'menu-open' : '' }}">
                            <a href="#"
                                class="nav-link {{ collect($item->children)->contains(fn($child) => request()->is($child->path)) ? 'active' : '' }}">
                                <i class="nav-icon {{ $item->icon }}"></i>
                                <p>
                                    {{ $item->title }}
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @foreach ($item->children as $child)
                                    <li class="nav-item">
                                        <a href="{{ url($child->path) }}"
                                            class="nav-link {{ request()->is($child->path) ? 'active' : '' }}">
                                            <i class="far fa-circle nav-icon"></i>
                                            <p>{{ $child->title }}</p>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <!-- Menu tanpa Submenu -->
                        <li class="nav-item">
                            <a href="{{ url($item->path) }}"
                                class="nav-link {{ request()->is($item->path) ? 'active' : '' }}">
                                <i class="nav-icon {{ $item->icon }}"></i>
                                <p>{{ $item->title }}</p>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>
</aside>
