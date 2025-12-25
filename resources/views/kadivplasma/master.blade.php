<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Kadiv Plasma') - WorkEase KCE</title>

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

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap 4 Theme CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />

    <!-- Tambahan Warna Sidebar Aktif -->
    <style>
        /* Custom styling untuk Select2 */
        .select2-container--bootstrap4 .select2-selection {
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            min-height: 38px;
            box-sizing: border-box;
            position: relative;
            overflow: visible !important;
        }
        .select2-container--bootstrap4 .select2-selection--single {
            height: 38px;
            box-sizing: border-box;
            position: relative;
            overflow: visible !important;
            display: flex !important;
            align-items: center !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
            line-height: 38px !important;
            height: 38px !important;
            padding-left: 12px;
            padding-right: 40px;
            display: flex !important;
            align-items: center !important;
            color: #495057 !important;
            visibility: visible !important;
            opacity: 1 !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            white-space: nowrap !important;
            box-sizing: border-box;
            position: relative;
            z-index: 10 !important;
            width: 100%;
            max-width: calc(100% - 40px);
            vertical-align: middle !important;
        }
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
            height: 38px;
            right: 8px;
            width: 20px;
            position: absolute;
            top: 0;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Pastikan Select2 container tidak melebihi parent */
        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap4 {
            width: 100% !important;
        }
        
        /* Fix untuk teks yang hilang - pastikan teks selalu terlihat */
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered,
        .select2-selection__rendered {
            color: #495057 !important;
            visibility: visible !important;
            opacity: 1 !important;
            display: flex !important;
            align-items: center !important;
            height: 38px !important;
            line-height: 38px !important;
        }
        
        /* Pastikan semua elemen di dalam rendered terlihat */
        .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered *,
        .select2-selection__rendered * {
            color: #495057 !important;
            visibility: visible !important;
            opacity: 1 !important;
            align-self: center !important;
        }
        
        /* Pastikan selection container memiliki width yang benar */
        .select2-container--bootstrap4 .select2-selection--single {
            width: 100%;
            overflow: visible !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Fix z-index untuk Select2 di dalam modal - harus lebih tinggi dari modal backdrop */
        .select2-container--open {
            z-index: 10050 !important; /* Modal backdrop biasanya 1040, modal 1050 */
        }

        .select2-dropdown {
            z-index: 10050 !important;
        }

        .modal .select2-container {
            z-index: 10050 !important;
        }
        
        .modal .select2-dropdown {
            z-index: 10050 !important;
        }

        .select2-search--dropdown {
            z-index: 10050 !important;
        }

        /* Pastikan Select2 dropdown tidak terpotong oleh modal */
        .modal {
            overflow-x: hidden !important;
            overflow-y: auto !important;
        }

        .modal-body {
            overflow-x: hidden !important;
            overflow-y: auto !important;
            max-height: calc(100vh - 210px);
        }

        .modal-content {
            overflow: visible !important;
        }
        
        /* Fix untuk modal scrolling - pastikan scrollbar berfungsi */
        .modal-dialog {
            max-height: calc(100vh - 60px);
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

        /* Scrollbar untuk Select2 dropdown */
        .select2-results {
            max-height: 200px !important;
            overflow-y: auto !important;
        }

        .select2-dropdown {
            max-height: 250px !important;
        }

        .select2-results__options {
            max-height: 200px !important;
            overflow-y: auto !important;
        }

        /* Styling scrollbar untuk browser modern */
        .select2-results__options::-webkit-scrollbar {
            width: 8px;
        }

        .select2-results__options::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .select2-results__options::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .select2-results__options::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Styling scrollbar untuk Firefox */
        .select2-results__options {
            scrollbar-width: thin;
            scrollbar-color: #888 #f1f1f1;
        }

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
                            <div class="d-sm-none d-lg-inline-block">{{ session('jabatan') ?? 'Kadiv Plasma' }}</div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <div class="dropdown-title">Logged in</div>
                            <a href="{{ route('kadivplasma.profile') }}" class="dropdown-item has-icon">
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
                        <a href="{{ route('kadivplasma.dashboard') }}">
                            <img src="{{ asset('assets/img/workease.png') }}" alt="KCE Logo" style="max-width: 140px;">
                        </a>
                    </div>
                    <div class="sidebar-brand sidebar-brand-sm">
                        <a href="{{ route('kadivplasma.dashboard') }}">
                            <img src="{{ asset('assets/img/KCE.png') }}" alt="KCE Logo" style="max-width: 35px;">
                        </a>
                    </div>

                    <ul class="sidebar-menu">
                        <li class="menu-header">Dashboard</li>
                        <li class="{{ request()->is('kadivplasma/dashboard') ? 'active' : '' }}">
                            <a href="{{ url('/kadivplasma/dashboard') }}" class="nav-link">
                                <i class="fas fa-fire"></i> <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-header">Work Order</li>
                        <li class="dropdown {{ request()->is('kadivplasma/work-order*') || request()->is('kadivplasma/daftar-work-order*') || request()->is('kadivplasma/riwayat-work-order*') ? 'active' : '' }}">
                            <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                                <i class="fas fa-briefcase"></i> <span>Work Order</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="{{ request()->is('kadivplasma/work-order') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/kadivplasma/work-order') }}">
                                        <i class="fas fa-plus-square"></i>
                                        <span>Ajukan Work Order</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('kadivplasma/daftar-work-order*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/kadivplasma/daftar-work-order') }}">
                                        <i class="fas fa-inbox"></i>
                                        <span>Work Order Masuk</span>
                                    </a>
                                </li>
                                <li class="{{ request()->is('kadivplasma/riwayat-work-order*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ url('/kadivplasma/riwayat-work-order') }}">
                                        <i class="fas fa-history"></i>
                                        <span>Riwayat Work Order</span>
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

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

