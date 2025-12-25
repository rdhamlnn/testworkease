<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Admin') - WorkEase KCE</title>

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
        
        /* Modal scrolling fix - pastikan scrollbar berfungsi */
        .modal {
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }
        
        .modal-body {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            max-height: calc(100vh - 210px);
        }
        
        .modal-open .modal {
            overflow-x: hidden;
            overflow-y: auto;
        }
        
        /* Scrollbar styling untuk modal */
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: #1B3C88;
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #0f2a5a;
        }
        
        /* Firefox scrollbar */
        .modal-body {
            scrollbar-width: thin;
            scrollbar-color: #1B3C88 #f1f1f1;
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
                            <div class="d-sm-none d-lg-inline-block">{{ session('jabatan') ?? 'Administrator' }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in</div>
                            <a href="{{ route('admin.profile') }}" class="dropdown-item has-icon">
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
                        <a href="{{ url('/admin/dashboard') }}">
                            <img src="{{ asset('assets/img/workease.png') }}" alt="KCE Logo" style="max-width: 140px;">
                        </a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="{{ url('/admin/dashboard') }}">
                            <img src="{{ asset('assets/img/KCE.png') }}" alt="KCE Logo" style="max-width: 35px;">
                        </a>
                    </div>

                    <ul class="sidebar-menu">
                        <li class="menu-header">Dashboard</li>
                        <li class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">
                            <a href="{{ url('/admin/dashboard') }}" class="nav-link">
                                <i class="fas fa-fire"></i> <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-header">Data Master</li>
                        <li class="dropdown {{ request()->is('admin/kelola-*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                                <i class="fas fa-database"></i> <span>Data Master</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="{{ request()->is('admin/kelola-akun') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/admin/kelola-akun') }}">
                                        <i class="fas fa-user-cog"></i>
                                        <span>Kelola Akun</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/kelola-divisi') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/admin/kelola-divisi') }}">
                                        <i class="fas fa-sitemap"></i>
                                        <span>Kelola Divisi</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/kelola-unit') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/admin/kelola-unit') }}">
                                        <i class="fas fa-industry"></i>
                                        <span>Kelola Unit</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/kelola-karyawan') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/admin/kelola-karyawan') }}">
                                        <i class="fas fa-users"></i>
                                        <span>Kelola Karyawan</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="menu-header">Work Order</li>
                        <li class="{{ request()->is('admin/work-order*') || request()->is('admin/daftar-pengajuan-work-order*') || request()->is('admin/laporan-arsip-wo*') ? 'active' : '' }}">
                            <a href="{{ url('/admin/work-order') }}" class="nav-link">
                                <i class="fas fa-briefcase"></i> <span>Work Order</span>
                            </a>
                        </li>

                        <li class="menu-header">Laporan & Arsip</li>
                        <li class="dropdown {{ request()->is('admin/laporan-harian-mekanik*') || request()->is('admin/laporan-pemakaian-barang*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                                <i class="fas fa-file-alt"></i> 
                                <span>Laporan & Arsip</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="{{ request()->is('admin/laporan-harian-mekanik*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/admin/laporan-harian-mekanik') }}">
                                        <i class="fas fa-calendar-check"></i>
                                        <span>Laporan Harian Mekanik</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('admin/laporan-pemakaian-barang*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/admin/laporan-pemakaian-barang') }}">
                                        <i class="fas fa-clipboard-list"></i>
                                        <span>Laporan Pemakaian Barang</span>
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
