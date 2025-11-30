<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Logistik') - WorkEase KCE</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/img/kce.png') }}">

    <!-- General CSS Files -->
    <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/fontawesome/css/all.min.css') }}">

    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{ asset('assets/modules/jqvmap/dist/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/weather-icon/css/weather-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/weather-icon/css/weather-icons-wind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/summernote/summernote-bs4.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/modules/datatables/DataTables-1.10.16/css/dataTables.bootstrap4.min.css') }}">

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/components.css') }}">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">

    <!-- Tambahan Warna Sidebar Aktif -->
    <style>
        /* Warna latar sidebar keseluruhan */
        .main-sidebar {
            background-color: #f8faff !important;
        }

        /* Warna item aktif di sidebar */
        .main-sidebar .sidebar-menu li.active>a {
            background-color: #1B3C88 !important;
            color: #fff !important;
            border-radius: 5px;
        }

        /* Warna ikon di item aktif */
        .main-sidebar .sidebar-menu li.active>a i {
            color: #fff !important;
        }

        /* Styling khusus untuk user profile di headbar */
        .nav-link-user {
            padding: 8px 12px !important;
            border-radius: 8px !important;
            transition: all 0.3s ease !important;
        }

        .nav-link-user:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            transform: translateY(-1px) !important;
        }

        .nav-link-user .d-flex {
            min-height: 36px;
        }

        .nav-link-user img {
            flex-shrink: 0;
        }

        .nav-link-user .d-sm-none {
            flex: 1;
            min-width: 0;
        }

        /* Hover efek */
        .main-sidebar .sidebar-menu li a:hover {
            background-color: #0056b3 !important;
            color: #fff !important;
        }

        /* Dropdown active state - parent menu */
        .main-sidebar .sidebar-menu li.dropdown.active > a {
            background-color: #1B3C88 !important;
            color: #fff !important;
            border-radius: 5px;
        }

        /* Dropdown active state - parent menu icon */
        .main-sidebar .sidebar-menu li.dropdown.active > a i {
            color: #fff !important;
        }

        /* Sub-menu active state */
        .main-sidebar .sidebar-menu .dropdown-menu li.active > a {
            background-color: #f8f9fa !important;
            color: #1B3C88 !important;
            font-weight: 600;
            border-left: 3px solid #1B3C88;
        }

        /* Sub-menu active state icon */
        .main-sidebar .sidebar-menu .dropdown-menu li.active > a i {
            color: #1B3C88 !important;
        }

        /* Sub-menu hover state */
        .main-sidebar .sidebar-menu .dropdown-menu li a:hover {
            background-color: #e9ecef !important;
            color: #1B3C88 !important;
        }

        /* Modal centered vertically */
        .modal-dialog {
            display: flex;
            align-items: center;
            min-height: calc(100% - 1rem);
        }

        @media (min-width: 576px) {
            .modal-dialog {
                min-height: calc(100% - 3.5rem);
            }
        }

        .modal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }

        /* Fix untuk text dropdown tidak wrap */
        .dropdown-menu .nav-link {
            white-space: nowrap !important;
        }
        /* Konsistensi tombol global */
        .btn-update {
            background-color: #1B3C88 !important;
            border-color: #1B3C88 !important;
            color: #fff !important;
            position: relative !important;
            z-index: 1 !important;
            transition: all 0.2s ease !important;
        }
        .btn-update:hover {
            background-color: #16316F !important;
            border-color: #16316F !important;
            color: #fff !important;
            opacity: 1 !important;
            transform: none !important;
        }
        .btn-change-password {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
            color: #fff !important;
            position: relative !important;
            z-index: 1 !important;
            transition: all 0.2s ease !important;
        }
        .btn-change-password:hover {
            background-color: #5a6268 !important;
            border-color: #545b62 !important;
            color: #fff !important;
            opacity: 1 !important;
            transform: none !important;
        }
        .btn-back {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: #fff !important;
            position: relative !important;
            z-index: 1 !important;
            transition: all 0.2s ease !important;
        }
        .btn-back:hover {
            background-color: #218838 !important;
            border-color: #1e7e34 !important;
            color: #fff !important;
            opacity: 1 !important;
            transform: none !important;
        }
        .btn-outline-secondary {
            position: relative !important;
            z-index: 1 !important;
            pointer-events: auto !important;
        }
        .btn-outline-secondary:hover {
            opacity: 1 !important;
            transform: none !important;
        }
    </style>

    @yield('styles')
    @stack('styles')
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">
            <!-- NAVBAR -->
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar">
                <form class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li>
                            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg">
                                <i class="fas fa-bars"></i>
                            </a>
                        </li>
                    </ul>
                </form>

                <ul class="navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <div class="d-sm-none d-lg-inline-block">{{ session('jabatan') ?? 'Logistik' }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in</div>
                            <a href="{{ route('logistik.profile') }}" class="dropdown-item has-icon">
                                <i class="far fa-user"></i> Profile
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ url('/logout') }}" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- SIDEBAR -->
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand">
                        <a href="{{ route('logistik.dashboard') }}">
                            <img src="{{ asset('assets/img/workease.png') }}" alt="KCE Logo" style="max-width: 140px;">
                        </a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="{{ route('logistik.dashboard') }}">
                            <img src="{{ asset('assets/img/KCE.png') }}" alt="KCE Logo" style="max-width: 35px;">
                        </a>
                    </div>

                    <ul class="sidebar-menu">
                        <li class="menu-header">Dashboard</li>
                        <li class="{{ request()->is('logistik/dashboard') ? 'active' : '' }}">
                            <a href="{{ url('/logistik/dashboard') }}" class="nav-link">
                                <i class="fas fa-fire"></i> <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-header">Work Order</li>
                        <li class="dropdown {{ request()->is('logistik/work-order*') || request()->is('logistik/daftar-work-order*') || request()->is('logistik/riwayat-work-order*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                                <i class="fas fa-briefcase"></i> <span>Work Order</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="{{ request()->is('logistik/work-order') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('logistik.work-order') }}">
                                        <i class="fas fa-plus-square"></i>
                                        <span>Ajukan Work Order</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('logistik/daftar-work-order*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('logistik.daftar-work-order') }}">
                                        <i class="fas fa-inbox"></i>
                                        <span>Work Order Masuk</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('logistik/riwayat-work-order*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('logistik.riwayat-work-order') }}">
                                        <i class="fas fa-history"></i>
                                        <span>Riwayat Work Order</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="menu-header">Barang</li>
                        <li class="dropdown {{ request()->is('logistik/terima-barang*') || request()->is('logistik/serahkan-barang*') || request()->is('logistik/permintaan-barang*') || request()->is('logistik/daftar-barang*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                                <i class="fas fa-box-open"></i> <span>Barang</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="{{ request()->is('logistik/daftar-barang*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/logistik/daftar-barang') }}">
                                        <i class="fas fa-list"></i>
                                        <span>Daftar Barang</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('logistik/permintaan-barang*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/logistik/permintaan-barang') }}">
                                        <i class="fas fa-shopping-cart"></i>
                                        <span>Permintaan Barang</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('logistik/terima-barang*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/logistik/terima-barang') }}">
                                        <i class="fas fa-box-open"></i>
                                        <span>Terima Barang</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('logistik/serahkan-barang*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/logistik/serahkan-barang') }}">
                                        <i class="fas fa-hand-holding"></i>
                                        <span>Serahkan Barang</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <li class="menu-header"></li>
                    </ul>
                </aside>
            </div>

            <!-- MAIN CONTENT -->
            <div class="main-content">
                @yield('content')
            </div>

            <!-- Notifications Component -->
            @include('components.notifications')

            <!-- Page Transition Component -->
            @include('components.page-transition')

            <!-- Modal Components -->
            @include('components.delete-confirm-modal')
            @include('components.approve-reject-confirm-modal')
            @include('components.confirm-modal')

            <!-- FOOTER -->
            <footer class="main-footer">
                <div class="footer-left">
                    Copyright &copy; {{ date('Y') }} <div class="bullet"></div> WorkEase KCE
                </div>
                <div class="footer-right">
                    Versi 1.0
                </div>
            </footer>
        </div>
    </div>

    <!-- General JS Scripts -->
    <script src="{{ asset('assets/modules/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/modules/popper.js') }}"></script>
    <script src="{{ asset('assets/modules/tooltip.js') }}"></script>
    <script src="{{ asset('assets/modules/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/modules/nicescroll/jquery.nicescroll.min.js') }}"></script>
    <script src="{{ asset('assets/modules/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/stisla.js') }}"></script>

    <!-- JS Libraries -->
    <script src="{{ asset('assets/modules/chart.min.js') }}"></script>
    <script src="{{ asset('assets/modules/summernote/summernote-bs4.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('assets/modules/datatables/DataTables-1.10.16/js/dataTables.bootstrap4.min.js') }}"></script>

    <!-- Template JS File -->
    <script src="{{ asset('assets/js/scripts.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    @yield('scripts')
    @stack('scripts')
</body>

</html>

