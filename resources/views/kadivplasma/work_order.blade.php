@extends('kadivplasma.master')

@section('title', 'Work Order')

@section('styles')
<style>
    .table th,
    .table td {
        text-align: left !important;
        vertical-align: middle;
    }

    .modal-header {
        background-color: #1B3C88 !important;
        color: #fff !important;
    }

    .modal-header .close {
        color: #fff !important;
        opacity: 1 !important;
    }

    .modal-header .close:hover {
        opacity: 0.8 !important;
    }

    .modal-body {
        padding: 20px 30px;
    }

    .modal-footer {
        padding: 15px 30px;
    }

    .btn-action {
        margin-right: 5px;
    }

    .btn-action:last-child {
        margin-right: 0;
    }

    /* Button Action - Simple & Clean dengan Font Awesome */
    .btn-view,
    .btn-edit,
    .btn-delete {
        min-width: 32px;
        padding: 4px 8px;
    }

    .btn-view i,
    .btn-edit i,
    .btn-delete i {
        font-size: 14px;
    }

    /* Hover effects */
    .btn-view:hover {
        background-color: #138496 !important;
        border-color: #117a8b !important;
    }

    .btn-edit:hover {
        background-color: #e0a800 !important;
        border-color: #d39e00 !important;
    }

    .btn-delete:hover {
        background-color: #c82333 !important;
        border-color: #bd2130 !important;
    }

    /* Pastikan field ditujukan bisa diklik saat enabled */
    #ditujukan:not(:disabled) {
        cursor: pointer !important;
        pointer-events: auto !important;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .priority-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .priority-high {
        background-color: #dc3545;
        color: white;
    }

    .priority-medium {
        background-color: #ffc107;
        color: #212529;
    }

    .priority-low {
        background-color: #28a745;
        color: white;
    }

    .status-pending {
        background-color: #ffc107;
        color: #212529;
    }

    .status-approved {
        background-color: #28a745;
        color: white;
    }

    .status-rejected {
        background-color: #dc3545;
        color: white;
    }

    .status-in-progress {
        background-color: #17a2b8;
        color: white;
    }

    .status-completed {
        background-color: #6c757d;
        color: white;
    }
    
    /* FIX empty table message to appear in first column (No) and left-aligned */
    .dataTables_empty,
    table.dataTable tbody tr td.dataTables_empty,
    table.dataTable tbody tr td:first-child.dataTables_empty,
    .dataTables_empty td,
    table.dataTable tbody tr td[colspan].dataTables_empty {
        text-align: left !important;
        padding-left: 15px !important;
        padding-right: 0 !important;
        margin: 0 !important;
        float: none !important;
        position: static !important;
        direction: ltr !important;
        unicode-bidi: normal !important;
        transform: none !important;
        justify-content: flex-start !important;
        align-items: flex-start !important;
        display: block !important;
        width: auto !important;
        max-width: none !important;
        box-sizing: border-box !important;
        colspan: 1 !important;
    }
    
    /* Force empty message to first column only */
    table.dataTable tbody tr td[colspan] {
        text-align: left !important;
        padding-left: 15px !important;
        direction: ltr !important;
        colspan: 1 !important;
    }
    
    /* Override any alignment styles */
    table.dataTable tbody tr td.dataTables_empty {
        text-align: left !important;
        padding-left: 15px !important;
        margin: 0 !important;
        display: block !important;
        width: auto !important;
        box-sizing: border-box !important;
    }
    
    /* Fix DataTable dropdown border bug */
    .dataTables_length select {
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
        padding: 0.375rem 2.25rem 0.375rem 0.75rem !important;
        font-size: 1rem !important;
        line-height: 1.5 !important;
        color: #495057 !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 16px 12px !important;
        appearance: none !important;
        width: auto !important;
        min-width: 80px !important;
    }
    
    .dataTables_length select:focus {
        border-color: #86b7fe !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

    .table-responsive {
        display: block !important;
        width: 100%;
        overflow-x: visible !important;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100% !important;
        min-width: 1000px !important;
        table-layout: auto;
        border-collapse: collapse !important;
    }

    .table th:last-child,
    .table td:last-child {
        white-space: nowrap !important;
        min-width: 180px !important;
        padding: 10px 8px !important;
    }
    
    
    .btn-icon {
        cursor: pointer !important;
        pointer-events: auto !important;
        position: relative;
        z-index: 10;
    }
    
    .btn-icon:disabled {
        cursor: not-allowed !important;
        opacity: 0.6;
    }
    
    .btn-edit,
    .btn-delete,
    .btn-view {
        cursor: pointer !important;
        pointer-events: auto !important;
        position: relative;
        z-index: 10;
    }

    .card-body {
        overflow-x: auto !important;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    .card-body > .table-responsive {
        min-width: 1000px !important;
    }

    .dataTables_wrapper {
        width: 100% !important;
        min-width: 1000px !important;
        overflow-x: visible;
        display: block !important;
    }

    .dataTables_wrapper > .row:first-child,
    .dataTables_wrapper > .row:last-child {
        min-width: 1000px !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
    }

    .dataTables_wrapper .row > div:last-child {
        display: block !important;
    }

    .dataTables_wrapper .row > div:last-child .dataTables_filter {
        float: right !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        white-space: nowrap !important;
    }
    
    /* Scrollable Tabs Styles */
    .nav-tabs-scrollable {
        display: flex;
        overflow-x: auto;
        overflow-y: hidden;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
        scrollbar-color: #1B3C88 #f1f1f1;
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 20px;
    }
    
    .nav-tabs-scrollable::-webkit-scrollbar {
        height: 8px;
    }
    
    .nav-tabs-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .nav-tabs-scrollable::-webkit-scrollbar-thumb {
        background: #1B3C88;
        border-radius: 4px;
    }
    
    .nav-tabs-scrollable::-webkit-scrollbar-thumb:hover {
        background: #0f2a5a;
    }
    
    .nav-tabs-scrollable .nav-item {
        flex-shrink: 0;
        margin-right: 5px;
    }
    
    .nav-tabs-scrollable .nav-link {
        white-space: nowrap;
        padding: 10px 20px;
        border: 1px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
        color: #495057;
        background-color: #f8f9fa;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    
    .nav-tabs-scrollable .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
        background-color: #e9ecef;
        color: #1B3C88;
    }
    
    .nav-tabs-scrollable .nav-link.active {
        color: #fff !important;
        background-color: #1B3C88;
        border-color: #1B3C88 #1B3C88 transparent;
        font-weight: 600;
    }
    
    .nav-tabs-scrollable .nav-link.active,
    .nav-tabs-scrollable .nav-link.active i,
    .nav-tabs-scrollable .nav-link.active .fas,
    .nav-tabs-scrollable .nav-link.active .fa {
        color: #fff !important;
    }
    
    .tab-content-scrollable {
        max-height: 450px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 5px;
    }
    
    .tab-content-scrollable::-webkit-scrollbar {
        width: 8px;
    }
    
    .tab-content-scrollable::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .tab-content-scrollable::-webkit-scrollbar-thumb {
        background: #1B3C88;
        border-radius: 4px;
    }
    
    .tab-content-scrollable::-webkit-scrollbar-thumb:hover {
        background: #0f2a5a;
    }
    
    /* Qty Input Styles */
    .qty-control-wrapper {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-left: 10px;
    }
    
    .qty-control-wrapper .btn-qty {
        width: 35px;
        height: 35px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #ced4da;
        background-color: #fff;
        color: #495057;
        font-weight: bold;
        cursor: pointer;
        border-radius: 4px;
    }
    
    .qty-control-wrapper .btn-qty:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
    }
    
    .qty-control-wrapper .btn-qty:active {
        background-color: #dee2e6;
    }
    
    .qty-control-wrapper .qty-display {
        width: 50px;
        height: 35px;
        text-align: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
        font-weight: 600;
        font-size: 14px;
    }
    
    .input-group .select2-container {
        flex: 1;
    }
    
    /* Stok Info Styles */
    .stok-info, .stok-info-edit {
        display: block;
        font-size: 12px;
        padding: 4px 8px;
        border-radius: 4px;
        margin-top: 4px;
        background-color: rgba(0,0,0,0.03);
    }
    
    .stok-info.text-success, .stok-info-edit.text-success {
        background-color: rgba(40, 167, 69, 0.1);
    }
    
    .stok-info.text-warning, .stok-info-edit.text-warning {
        background-color: rgba(255, 193, 7, 0.15);
    }
    
    .stok-info.text-danger, .stok-info-edit.text-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }
    
    /* View Work Order Modal - Barang Table Styles */
    #viewWorkOrderModal .modal-body {
        max-height: 80vh;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding: 20px 30px;
        scrollbar-width: thin;
        scrollbar-color: #1B3C88 #f1f1f1;
    }
    
    #viewWorkOrderModal .modal-body::-webkit-scrollbar {
        width: 10px;
        -webkit-appearance: none;
    }
    
    #viewWorkOrderModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.1);
    }
    
    #viewWorkOrderModal .modal-body::-webkit-scrollbar-thumb {
        background: #1B3C88;
        border-radius: 5px;
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
    }
    
    #viewWorkOrderModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #0f2a5a;
    }
    
    #view_barang_container {
        width: 100%;
        overflow: hidden;
    }
    
    #view_barang_table {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: auto;
        width: 100%;
        position: relative;
        display: block;
    }
    
    #view_barang_table table {
        width: 100% !important;
        margin-bottom: 0;
        min-width: 100% !important;
        table-layout: fixed;
        border-collapse: collapse;
    }
    
    #view_barang_table table thead {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    
    #view_barang_table table tbody {
        display: block;
        max-height: 350px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    #view_barang_table table tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    
    #view_barang_table table th:nth-child(3),
    #view_barang_table table td:nth-child(3) {
        text-align: center !important;
        padding: 8px 12px;
        font-size: 14px;
        word-wrap: break-word;
    }
    
    #view_barang_table table th {
        background-color: #f8f9fa;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
        border-bottom: 2px solid #dee2e6;
    }
    
    #view_barang_table table tbody::-webkit-scrollbar {
        width: 8px;
    }
    
    #view_barang_table table tbody::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    #view_barang_table table tbody::-webkit-scrollbar-thumb {
        background: #1B3C88;
        border-radius: 4px;
    }
    
    #view_barang_table table tbody::-webkit-scrollbar-thumb:hover {
        background: #0f2a5a;
    }
    
    #view_barang_table::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    #view_barang_table::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    #view_barang_table::-webkit-scrollbar-thumb {
        background: #1B3C88;
        border-radius: 4px;
    }
    
    #view_barang_table::-webkit-scrollbar-thumb:hover {
        background: #0f2a5a;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('kadivplasma.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Work Order</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <div class="row">
            <div class="col-12">
                <!-- Work Order -->
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Work Order</h4>
                        <div class="card-header-action">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#tambahWorkOrderModal">
                                <i class="fas fa-plus"></i> Tambah Work Order
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="workOrderTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>No Work-Order</th>
                                <th>Jenis WO</th>
                                <th>Divisi Pengaju</th>
                                <th>Hari/Tanggal</th>
                                <th>Unit/Code</th>
                                <th>Uraian</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workOrders as $i => $wo)
                                @php
                                    $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                                    $statusLabel = $status ?? 'Menunggu';
                                    $statusLower = strtolower($statusLabel);
                                    $isLocked = in_array($wo->id_verifikator, [2, 3]);
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $wo->no_surat_pengajuan }}</td>
                                    <td>
                                        @if($wo->jenisWorkOrder)
                                            {{ $wo->jenisWorkOrder->nama_jenis_wo }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $wo->divisi_pengaju }}</td>
                                    <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                    <td>{{ $wo->unit }}</td>
                                    <td>{{ Str::limit($wo->uraian, 30) }}</td>
                                    <td>
                                        @if($status == 'Disetujui' || $status == 'Selesai')
                                            <span class="badge badge-success">{{ $status }}</span>
                                        @elseif($status == 'Ditolak')
                                            <span class="badge badge-danger">{{ $status }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ $status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-info btn-sm btn-view" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            @if($status == 'Menunggu')
                                            <button type="button" class="btn btn-warning btn-sm btn-edit" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" title="Edit"
                                                data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                data-lock-message="{{ $isLocked ? 'Work Order tidak dapat diedit karena status sudah '.$statusLower.'.' : '' }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-delete" 
                                                data-url="{{ route('kadivplasma.hapus-work-order', $wo->id_surat_pengajuan) }}"
                                                data-message="Yakin ingin menghapus work order ini?" title="Hapus"
                                                data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                data-lock-message="{{ $isLocked ? 'Work Order tidak dapat dihapus karena status sudah '.$statusLower.'.' : '' }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @elseif($status == 'Ditolak')
                                            {{-- Button Edit untuk Work Order yang Dit olak --}}
                                            <button type="button" class="btn btn-warning btn-sm btn-edit" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" title="Edit Work Order">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            {{-- Button Kirim Ulang Ajuan untuk WO yang ditolak --}}
                                            <button type="button" class="btn btn-success btn-sm btn-resend" 
                                                data-id="{{ $wo->id_surat_pengajuan }}"
                                                data-url="{{ route('kadivplasma.work-order.resend', $wo->id_surat_pengajuan) }}"
                                                title="Kirim Ulang Ajuan">
                                                <i class="fas fa-paper-plane"></i> Kirim Ulang
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">Belum ada data work order</td>
                                </tr>
                            @endforelse
                        </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah Work Order -->
<div class="modal fade" id="tambahWorkOrderModal" tabindex="-1" role="dialog" aria-labelledby="tambahWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahWorkOrderModalLabel">Tambah Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="tambahWorkOrderForm" action="{{ route('kadivplasma.work-order.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Scrollable Tabs Navigation -->
                    <ul class="nav nav-tabs nav-tabs-scrollable" id="tambahWorkOrderTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="info-dasar-tab" data-toggle="tab" href="#info-dasar" role="tab" aria-controls="info-dasar" aria-selected="true">
                                <i class="fas fa-info-circle"></i> Informasi Dasar
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="detail-wo-tab" data-toggle="tab" href="#detail-wo" role="tab" aria-controls="detail-wo" aria-selected="false">
                                <i class="fas fa-clipboard-list"></i> Detail Work Order
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="dokumentasi-tab" data-toggle="tab" href="#dokumentasi" role="tab" aria-controls="dokumentasi" aria-selected="false">
                                <i class="fas fa-file-upload"></i> Dokumentasi
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content tab-content-scrollable" id="tambahWorkOrderTabContent">
                        <!-- Tab 1: Informasi Dasar -->
                        <div class="tab-pane fade show active" id="info-dasar" role="tabpanel" aria-labelledby="info-dasar-tab">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_work_order">No. Work Order</label>
                                        <input type="text" class="form-control" id="no_work_order" name="no_work_order" readonly value="{{ $nextWorkOrderNumber ?? '01/MKN/KCE/2025' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="divisi_pengaju">Divisi Pengaju <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="divisi_pengaju" value="{{ $divisiPengaju }}" readonly>
                                        <input type="hidden" name="divisi_pengaju" value="{{ $divisiPengaju }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="id_jenis_wo">Jenis Work Order <span class="text-danger">*</span></label>
                                        <select class="form-control" name="id_jenis_wo" id="id_jenis_wo" required autofocus>
                                            <option value="">-- Pilih Jenis Work Order --</option>
                                            @foreach($jenisWorkOrder as $jenis)
                                                <option value="{{ $jenis->id_jenis_wo }}" data-nama-jenis="{{ $jenis->nama_jenis_wo }}">{{ $jenis->nama_jenis_wo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 2: Detail Work Order -->
                        <div class="tab-pane fade" id="detail-wo" role="tabpanel" aria-labelledby="detail-wo-tab">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="ditujukan" id="ditujukan" required disabled>
                                            <option value="">-- Pilih Jenis Work Order terlebih dahulu --</option>
                                            <!-- Options untuk Pembelian: purchasing → Logistik -->
                                            <option value="Logistik" data-jenis-wo="pembelian" style="display: none;">Logistik</option>
                                            <!-- Options untuk Perbaikan: ditujukan ke Mekanik -->
                                            <option value="Mekanik" data-jenis-wo="perbaikan" style="display: none;">Mekanik</option>
                                            <!-- Options untuk Permintaan: semua kecuali Atasan, Purchasing, Admin, dan Mekanik sendiri -->
                                            <option value="Produksi" data-jenis-wo="permintaan" style="display: none;">Produksi</option>
                                            <option value="Plasma" data-jenis-wo="permintaan" style="display: none;">Plasma</option>
                                            <option value="Quality Control" data-jenis-wo="permintaan" style="display: none;">Quality Control</option>
                                            <option value="Logistik" data-jenis-wo="permintaan" style="display: none;">Logistik</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="unit" id="label_unit">Nama Unit / Code <span class="text-danger">*</span></label>
                                        <!-- Toggle untuk Perbaikan Unit (hanya muncul jika jenis WO = Perbaikan) -->
                                        <div id="perbaikan_unit_toggle" class="mb-2" style="display: none;">
                                            <span class="mr-3">Perbaikan unit atau tidak?</span>
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-outline-primary btn-sm">
                                                    <input type="radio" name="is_perbaikan_unit" id="is_perbaikan_unit_yes" value="1" autocomplete="off"> Unit
                                                </label>
                                                <label class="btn btn-outline-secondary btn-sm active">
                                                    <input type="radio" name="is_perbaikan_unit" id="is_perbaikan_unit_no" value="0" autocomplete="off" checked> Tidak
                                                </label>
                                            </div>
                                        </div>
                                        <!-- Select2 untuk memilih unit (muncul jika pilih "Unit") -->
                                        <select class="form-control select2-unit" id="unit_select" name="unit" style="display: none;">
                                            <option value="">-- Pilih Unit --</option>
                                            @foreach($unit as $u)
                                                <option value="{{ $u->nama_unit }}">{{ $u->nama_unit }}</option>
                                            @endforeach
                                        </select>
                                        <!-- Input text (default, muncul jika pilih "Tidak") -->
                                        <input type="text" class="form-control" id="unit" name="unit" required placeholder="Masukkan keterangan">
                                        <!-- Container untuk Select2 dinamis (jenis work order Pembelian) -->
                                        <div id="unit_pembelian_container" style="display: none;"></div>
                                        <!-- Template tersembunyi untuk option barang -->
                                        <select id="template_barang_options" style="display: none;">
                                            @foreach($daftarBarang as $barang)
                                                <option value="{{ $barang->nama_barang }}" data-stok="{{ $barang->stok ?? 0 }}" data-satuan="{{ $barang->satuan ?? '' }}">{{ $barang->nama_barang }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="uraian">Uraian <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="uraian" name="uraian" rows="4" required placeholder="Masukkan uraian work order"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 3: Dokumentasi -->
                        <div class="tab-pane fade" id="dokumentasi" role="tabpanel" aria-labelledby="dokumentasi-tab">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="dokumentasi">Dokumentasi</label>
                                        <input type="file" class="form-control" id="dokumentasi" name="dokumentasi" accept=".pdf,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Format: JPG, PNG, PDF. Maksimal 2MB.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="reset" class="btn btn-warning">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Work Order -->
<div class="modal fade" id="editWorkOrderModal" tabindex="-1" role="dialog" aria-labelledby="editWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWorkOrderModalLabel">Edit Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editWorkOrderForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <!-- Scrollable Tabs Navigation -->
                    <ul class="nav nav-tabs nav-tabs-scrollable" id="editWorkOrderTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="edit-info-dasar-tab" data-toggle="tab" href="#edit-info-dasar" role="tab" aria-controls="edit-info-dasar" aria-selected="true">
                                <i class="fas fa-info-circle"></i> Informasi Dasar
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="edit-detail-wo-tab" data-toggle="tab" href="#edit-detail-wo" role="tab" aria-controls="edit-detail-wo" aria-selected="false">
                                <i class="fas fa-clipboard-list"></i> Detail Work Order
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="edit-dokumentasi-tab" data-toggle="tab" href="#edit-dokumentasi" role="tab" aria-controls="edit-dokumentasi" aria-selected="false">
                                <i class="fas fa-file-upload"></i> Dokumentasi
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content tab-content-scrollable" id="editWorkOrderTabContent">
                        <!-- Tab 1: Informasi Dasar -->
                        <div class="tab-pane fade show active" id="edit-info-dasar" role="tabpanel" aria-labelledby="edit-info-dasar-tab">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_no_work_order">No. Work Order</label>
                                        <input type="text" class="form-control" id="edit_no_work_order" name="no_work_order" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_tanggal">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_divisi_pengaju">Divisi Pengaju</label>
                                        <input type="text" class="form-control" id="edit_divisi_pengaju" name="divisi_pengaju" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_id_jenis_wo">Jenis Work Order <span class="text-danger">*</span></label>
                                        <select class="form-control" name="id_jenis_wo" id="edit_id_jenis_wo" required autofocus>
                                            <option value="">-- Pilih Jenis Work Order --</option>
                                            @foreach($jenisWorkOrder as $jenis)
                                                <option value="{{ $jenis->id_jenis_wo }}" data-nama-jenis="{{ $jenis->nama_jenis_wo }}">{{ $jenis->nama_jenis_wo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 2: Detail Work Order -->
                        <div class="tab-pane fade" id="edit-detail-wo" role="tabpanel" aria-labelledby="edit-detail-wo-tab">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="edit_ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="ditujukan" id="edit_ditujukan" required disabled>
                                            <option value="">-- Pilih Jenis Work Order terlebih dahulu --</option>
                                            <!-- Options untuk Pembelian: purchasing → Logistik -->
                                            <option value="Logistik" data-jenis-wo="pembelian" style="display: none;">Logistik</option>
                                            <!-- Options untuk Perbaikan: ditujukan ke Mekanik -->
                                            <option value="Mekanik" data-jenis-wo="perbaikan" style="display: none;">Mekanik</option>
                                            <!-- Options untuk Permintaan: semua kecuali Atasan, Purchasing, Admin, dan Mekanik sendiri -->
                                            <option value="Produksi" data-jenis-wo="permintaan" style="display: none;">Produksi</option>
                                            <option value="Plasma" data-jenis-wo="permintaan" style="display: none;">Plasma</option>
                                            <option value="Quality Control" data-jenis-wo="permintaan" style="display: none;">Quality Control</option>
                                            <option value="Logistik" data-jenis-wo="permintaan" style="display: none;">Logistik</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="edit_unit" id="edit_label_unit">Nama Unit / Code <span class="text-danger">*</span></label>
                                        <!-- Toggle untuk Perbaikan Unit (hanya muncul jika jenis WO = Perbaikan) -->
                                        <div id="edit_perbaikan_unit_toggle" class="mb-2" style="display: none;">
                                            <span class="mr-3">Perbaikan unit atau tidak?</span>
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-outline-primary btn-sm">
                                                    <input type="radio" name="edit_is_perbaikan_unit" id="edit_is_perbaikan_unit_yes" value="1" autocomplete="off"> Unit
                                                </label>
                                                <label class="btn btn-outline-secondary btn-sm active">
                                                    <input type="radio" name="edit_is_perbaikan_unit" id="edit_is_perbaikan_unit_no" value="0" autocomplete="off" checked> Tidak
                                                </label>
                                            </div>
                                        </div>
                                        <!-- Select2 untuk memilih unit (muncul jika perbaikan unit = Ya) -->
                                        <select class="form-control select2-unit" id="edit_unit_select" name="unit" style="display: none;">
                                            <option value="">-- Pilih Unit --</option>
                                            @foreach($unit as $u)
                                                <option value="{{ $u->nama_unit }}">{{ $u->nama_unit }}</option>
                                            @endforeach
                                        </select>
                                        <!-- Input text untuk non-unit (default) -->
                                        <input type="text" class="form-control" id="edit_unit" name="unit" required>
                                        <!-- Container untuk Select2 dinamis (jenis work order Pembelian) -->
                                        <div id="edit_unit_pembelian_container" style="display: none;"></div>
                                        <!-- Template tersembunyi untuk option barang -->
                                        <select id="edit_template_barang_options" style="display: none;">
                                            @foreach($daftarBarang as $barang)
                                                <option value="{{ $barang->nama_barang }}" data-stok="{{ $barang->stok ?? 0 }}" data-satuan="{{ $barang->satuan ?? '' }}">{{ $barang->nama_barang }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="edit_uraian">Uraian <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="edit_uraian" name="uraian" rows="4" required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 3: Dokumentasi -->
                        <div class="tab-pane fade" id="edit-dokumentasi" role="tabpanel" aria-labelledby="edit-dokumentasi-tab">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="edit_dokumentasi">Dokumentasi</label>
                                        <input type="file" class="form-control" id="edit_dokumentasi" name="dokumentasi" accept=".pdf,.jpg,.jpeg,.png" onchange="previewDokumentasiEditKadiv(this)">
                                        <small class="form-text text-muted">Format: JPG, PNG, PDF. Maks. 2MB</small>
                                        <div id="previewDokumentasiContainerKadiv" class="mt-2" style="display: none;">
                                            <img id="previewDokumentasiKadiv" src="" alt="Preview Dokumentasi" style="max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Work Order -->
<div class="modal fade" id="viewWorkOrderModal" tabindex="-1" role="dialog" aria-labelledby="viewWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewWorkOrderModalLabel">Detail Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>No. Surat Pengajuan:</strong></label>
                            <p id="view_no_wo" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Tanggal:</strong></label>
                            <p id="view_tanggal" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Divisi Pengaju:</strong></label>
                            <p id="view_divisi_pengaju" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Ditujukan:</strong></label>
                            <p id="view_ditujukan" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Jenis Work Order:</strong></label>
                            <p id="view_jenis_wo" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label id="view_label_unit"><strong>Unit:</strong></label>
                            <p id="view_unit" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="view_barang_container" style="display: none;">
                    <label><strong>Barang:</strong></label>
                    <div id="view_barang_table" class="border rounded" style="padding: 0; overflow: hidden;"></div>
                </div>
                <div class="form-group">
                    <label><strong>Uraian:</strong></label>
                    <p id="view_uraian" class="form-control-plaintext border p-2 rounded"></p>
                </div>
                <div class="form-group">
                    <label><strong>Dokumentasi:</strong></label>
                    <div id="view_dokumentasi" class="form-control-plaintext border p-2 rounded"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tutup otomatis alert setelah 3 detik
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);

    function isLockedAction(element) {
        const raw = $(element).data('locked');
        return raw === true || raw === 'true';
    }

    function notifyLockedAction(element, fallbackMessage) {
        const message = $(element).data('lockMessage') || fallbackMessage || 'Work Order tidak dapat diproses karena status saat ini.';
        if (typeof window.triggerToast === 'function') {
            window.triggerToast(message, 'danger', 5200);
        } else {
            alert(message);
        }
    }

    // Initialize Select2 for unit dropdown
    function initSelect2UnitDropdown(selector) {
        if ($(selector).hasClass('select2-hidden-accessible')) {
            const currentValue = $(selector).val();
            $(selector).select2('destroy');
            if (currentValue) {
                $(selector).val(currentValue);
            }
        }
        
        let dropdownParent = $(selector).closest('.modal');
        if (dropdownParent.length === 0) {
            dropdownParent = $(document.body);
        }
        
        const savedValue = $(selector).val();
        
        $(selector).select2({
            theme: 'bootstrap4',
            placeholder: 'Cari dan pilih unit...',
            allowClear: true,
            width: '100%',
            dropdownParent: dropdownParent,
            language: {
                noResults: function() { return "Tidak ada hasil"; },
                searching: function() { return "Mencari..."; }
            }
        });
        
        if (savedValue) {
            $(selector).val(savedValue).trigger('change');
        }
    }

    // Toggle perbaikan unit field
    function togglePerbaikanUnit(isEdit = false) {
        const prefix = isEdit ? 'edit_' : '';
        const jenisWoSelect = $(`#${prefix}id_jenis_wo`);
        const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis') || jenisWoSelect.find('option:selected').text();
        const isPerbaikan = selectedJenisWo && selectedJenisWo.toLowerCase() === 'perbaikan';
        
        const toggle = $(`#${prefix}perbaikan_unit_toggle`);
        const unitSelect = $(`#${prefix}unit_select`);
        const unitInput = $(`#${prefix}unit`);
        
        if (isPerbaikan) {
            // Hide pembelian container
            $(`#${prefix}unit_pembelian_container`).hide().html('');
            toggle.show();
            const isPerbaikanUnit = $(`#${prefix}is_perbaikan_unit_yes`).is(':checked');
            updateUnitFieldVisibility(isEdit, isPerbaikanUnit);
        } else {
            toggle.hide();
            $(`#${prefix}is_perbaikan_unit_no`).prop('checked', true).parent().addClass('active');
            $(`#${prefix}is_perbaikan_unit_yes`).prop('checked', false).parent().removeClass('active');
            
            // Hanya tampilkan unit input jika bukan Pembelian
            const isPembelian = selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian';
            if (!isPembelian) {
                unitInput.show().attr('name', 'unit').attr('required', 'required');
            }
            unitSelect.hide().removeAttr('name').removeAttr('required');
            if (unitSelect.hasClass('select2-hidden-accessible')) {
                unitSelect.select2('destroy');
            }
        }
    }
    
    function updateUnitFieldVisibility(isEdit, isPerbaikanUnit) {
        const prefix = isEdit ? 'edit_' : '';
        const unitSelect = $(`#${prefix}unit_select`);
        const unitInput = $(`#${prefix}unit`);
        
        if (isPerbaikanUnit) {
            unitInput.hide().removeAttr('name').removeAttr('required');
            unitSelect.show().attr('name', 'unit').attr('required', 'required');
            initSelect2UnitDropdown(`#${prefix}unit_select`);
        } else {
            unitInput.show().attr('name', 'unit').attr('required', 'required');
            unitSelect.hide().removeAttr('name').removeAttr('required');
            if (unitSelect.hasClass('select2-hidden-accessible')) {
                unitSelect.select2('destroy');
            }
        }
    }

    $(document).on('change', '#id_jenis_wo', function() {
        togglePerbaikanUnit(false);
    });
    
    $(document).on('change', '#edit_id_jenis_wo', function() {
        togglePerbaikanUnit(true);
    });

    $(document).on('change', 'input[name="is_perbaikan_unit"]', function() {
        updateUnitFieldVisibility(false, $(this).val() === '1');
    });
    
    $(document).on('change', 'input[name="edit_is_perbaikan_unit"]', function() {
        updateUnitFieldVisibility(true, $(this).val() === '1');
    });

    $(document).on('hidden.bs.modal', '#tambahWorkOrderModal, #editWorkOrderModal', function() {
        $(this).find('.select2-unit').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });
        $('#perbaikan_unit_toggle').hide();
        $('#edit_perbaikan_unit_toggle').hide();
    })

    // ============================================
    // FUNGSI HELPER GLOBAL - DI LUAR document.ready
    // ============================================
    // Fungsi-fungsi ini harus di global scope agar bisa diakses dari editWorkOrder
    
    // Clear semua select2 dinamis
    // Pastikan tersedia di window object untuk akses global
    window.clearUnitSelects = function(isEdit = false) {
        try {
            const containerId = isEdit ? 'edit_unit_pembelian_container' : 'unit_pembelian_container';
            const container = $('#' + containerId);
            
            if (container.length === 0) return; // Safety check
            
            // Destroy semua Select2
            container.find('.select2-unit-dynamic').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            
            // Clear container
            container.empty();
        } catch (error) {
            console.error('Error in clearUnitSelects:', error);
        }
    };
    
    // Alias untuk kompatibilitas
    var clearUnitSelects = window.clearUnitSelects;
    
    // Template HTML untuk select2 dinamis
    window.getUnitSelectTemplate = function(index, isEdit = false) {
        try {
            const btnClass = isEdit ? 'btn-tambah-barang-edit' : 'btn-tambah-barang';
            const btnHapusClass = isEdit ? 'btn-hapus-barang-edit' : 'btn-hapus-barang';
            const $template = $('#template_barang_options');
            const optionsHtml = $template.length > 0 ? $template.html() : '';
            const qtyBtnClass = isEdit ? 'btn-qty-minus-edit' : 'btn-qty-minus';
            const qtyPlusBtnClass = isEdit ? 'btn-qty-plus-edit' : 'btn-qty-plus';
            const qtyInputClass = isEdit ? 'qty-input-edit' : 'qty-input';
            
            const stokInfoClass = isEdit ? 'stok-info-edit' : 'stok-info';
            
            return `
                <div class="unit-select-wrapper mb-2" data-index="${index}">
                    <div class="input-group" style="display: flex; align-items: center;">
                        <select class="form-control select2-unit-dynamic" name="unit[]" data-index="${index}" style="flex: 1;">
                            <option value="">-- Pilih Barang --</option>
                            ${optionsHtml}
                        </select>
                        <div class="qty-control-wrapper" style="display: none;">
                            <button type="button" class="btn-qty ${qtyBtnClass}" data-index="${index}" title="Kurangi Qty">
                                <span style="font-size: 18px;">−</span>
                            </button>
                            <div class="qty-display">
                                <span class="${qtyInputClass}" data-index="${index}">1</span>
                            </div>
                            <button type="button" class="btn-qty ${qtyPlusBtnClass}" data-index="${index}" title="Tambah Qty">
                                <span style="font-size: 18px;">+</span>
                            </button>
                        </div>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success btn-sm ${btnClass}" title="Tambah Barang" style="display: none;">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <button type="button" class="btn btn-danger btn-sm ${btnHapusClass}" title="Hapus" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <span class="${stokInfoClass} text-muted" data-index="${index}" style="font-size: 12px; display: none; margin-top: 4px;">
                        <i class="fas fa-box"></i> Stok: <strong class="stok-value">0</strong> <span class="stok-satuan"></span>
                    </span>
                </div>
            `;
        } catch (error) {
            console.error('Error in getUnitSelectTemplate:', error);
            return '';
        }
    };
    var getUnitSelectTemplate = window.getUnitSelectTemplate;
    
    // Inisialisasi Select2 untuk select2 dinamis
    window.initSelect2Dynamic = function(selector) {
        try {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            
            let dropdownParent = $(selector).closest('.modal');
            if (dropdownParent.length === 0) {
                dropdownParent = $(document.body);
            }
            
            const savedValue = $(selector).val();
            
            $(selector).select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih Barang...',
                allowClear: true,
                width: '100%',
                dropdownParent: dropdownParent,
                language: {
                    noResults: function() { return "Tidak ada hasil"; },
                    searching: function() { return "Mencari..."; }
                }
            });
            
            $(selector).off('change.select2-dynamic').on('change.select2-dynamic', function() {
                const $wrapper = $(this).closest('.unit-select-wrapper');
                const isEdit = $wrapper.closest('#edit_unit_pembelian_container').length > 0;
                if (typeof window.updateHapusButtonVisibility === 'function') {
                    window.updateHapusButtonVisibility(isEdit);
                }
                
                const $qtyControl = $wrapper.find('.qty-control-wrapper');
                const $stokInfo = $wrapper.find('.stok-info, .stok-info-edit');
                
                if ($(this).val() && $(this).val() !== '') {
                    $qtyControl.show();
                    
                    // Get stok data from template option
                    const selectedValue = $(this).val();
                    const $templateOption = $('#template_barang_options option[value="' + selectedValue + '"]');
                    
                    if ($templateOption.length > 0) {
                        const stok = $templateOption.data('stok') || 0;
                        const satuan = $templateOption.data('satuan') || '';
                        
                        // Update stok info display
                        $stokInfo.find('.stok-value').text(stok);
                        $stokInfo.find('.stok-satuan').text(satuan);
                        $stokInfo.show();
                        
                        // Add color based on stock level
                        if (stok <= 0) {
                            $stokInfo.removeClass('text-muted text-success').addClass('text-danger');
                            $stokInfo.find('.stok-value').parent().html('<i class="fas fa-exclamation-triangle"></i> Stok: <strong class="stok-value">' + stok + '</strong> <span class="stok-satuan">' + satuan + '</span> (Habis)');
                        } else if (stok <= 10) {
                            $stokInfo.removeClass('text-muted text-danger').addClass('text-warning');
                            $stokInfo.html('<i class="fas fa-box"></i> Stok: <strong class="stok-value">' + stok + '</strong> <span class="stok-satuan">' + satuan + '</span> (Stok Rendah)');
                        } else {
                            $stokInfo.removeClass('text-danger text-warning').addClass('text-success');
                            $stokInfo.html('<i class="fas fa-box"></i> Stok: <strong class="stok-value">' + stok + '</strong> <span class="stok-satuan">' + satuan + '</span>');
                        }
                    }
                } else {
                    $qtyControl.hide();
                    $stokInfo.hide();
                    $wrapper.find('.qty-input, .qty-input-edit').text('1');
                }
            });
            
            if (savedValue) {
                $(selector).val(savedValue).trigger('change.select2-dynamic');
            }
        } catch (error) {
            console.error('Error in initSelect2Dynamic:', error);
        }
    };
    var initSelect2Dynamic = window.initSelect2Dynamic;
    
    // Toggle field unit untuk form edit
    window.toggleUnitFieldEdit = function() {
        try {
            const jenisWoSelect = $('#edit_id_jenis_wo');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            const unitInput = $('#edit_unit');
            const unitContainer = $('#edit_unit_pembelian_container');
            const unitLabel = $('#label_edit_unit');
            
            if (selectedJenisWo && (selectedJenisWo.toLowerCase() === 'pembelian' || selectedJenisWo.toLowerCase() === 'perbaikan')) {
                unitInput.hide().removeAttr('required').removeAttr('name');
                unitContainer.show();
                
                if (selectedJenisWo.toLowerCase() === 'pembelian') {
                    unitLabel.html('Daftar barang <span class="text-danger">*</span>');
                } else {
                    unitLabel.html('Daftar barang <small class="text-muted">(Opsional)</small>');
                }
                
                if (unitContainer.find('.unit-select-wrapper').length === 0) {
                    unitContainer.html(getUnitSelectTemplate(0, true));
                    const firstSelect = unitContainer.find('.select2-unit-dynamic[data-index="0"]');
                    initSelect2Dynamic(firstSelect);
                } else {
                    unitContainer.find('.select2-unit-dynamic').each(function() {
                        initSelect2Dynamic($(this));
                    });
                }
            } else {
                unitInput.show().attr('required', 'required').attr('name', 'unit');
                unitContainer.hide();
                clearUnitSelects(true);
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
            }
        } catch (error) {
            console.error('Error in toggleUnitFieldEdit:', error);
        }
    };
    var toggleUnitFieldEdit = window.toggleUnitFieldEdit;
    
    // Toggle section barang untuk form edit
    window.toggleBarangSectionEdit = function() {
        try {
            $('#edit-barang-section').hide();
            $('#edit-daftar-barang-container').empty();
        } catch (error) {
            console.error('Error in toggleBarangSectionEdit:', error);
        }
    };
    var toggleBarangSectionEdit = window.toggleBarangSectionEdit;

    // Initialize DataTable
    $(document).ready(function() {
        $('#workOrderTable').DataTable({
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "searching": true,
            "ordering": true,
            "info": true,
            "paging": true,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                },
                "emptyTable": "Tidak ada data work order"
            }
        });

        // Pastikan event handler tetap bekerja setelah DataTable di-render ulang
        $('#workOrderTable').on('draw.dt', function() {
            // Re-bind event handlers setelah DataTable redraw
        });

        // Pastikan button bisa diklik dengan menambahkan event handler langsung ke table
        $('#workOrderTable tbody').on('click', '.btn-view', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id');
            if (id && typeof viewWorkOrder === 'function') {
                viewWorkOrder(id);
            }
        });

        $('#workOrderTable tbody').on('click', '.btn-edit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isLockedAction(this)) {
                notifyLockedAction(this, 'Work Order tidak dapat diedit karena status sudah final.');
                return false;
            }

            var id = $(this).data('id');
            if (id && typeof editWorkOrder === 'function') {
                editWorkOrder(id);
            }
            return false;
        });

        // Set tanggal hari ini untuk form tambah
        var today = new Date().toISOString().split('T')[0];
        $('#tanggal').val(today);

        // Nonaktifkan enforceFocus Bootstrap untuk kompatibilitas dengan Select2
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};

        // Inisialisasi Select2 untuk combobox unit
        function initSelect2Unit(selector) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                // Simpan nilai sebelum destroy
                const currentValue = $(selector).val();
                $(selector).select2('destroy');
                // Set nilai kembali setelah destroy
                if (currentValue) {
                    $(selector).val(currentValue);
                }
            }
            
            // Tentukan parent modal berdasarkan selector
            let dropdownParent = $(selector).closest('.modal');
            if (dropdownParent.length === 0) {
                dropdownParent = $(document.body);
            }
            
            // Simpan nilai sebelum initialize
            const savedValue = $(selector).val();
            
            $(selector).select2({
                theme: 'bootstrap4',
                placeholder: 'Cari dan pilih barang...',
                allowClear: true,
                width: '100%',
                multiple: true,
                dropdownParent: dropdownParent, // Render dropdown di dalam modal
                language: {
                    noResults: function() {
                        return "Tidak ada hasil";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            });
            
            // Set nilai kembali setelah initialize
            if (savedValue) {
                $(selector).val(savedValue).trigger('change');
            }
            
            // Event handler untuk memastikan nilai tetap terlihat setelah select
            $(selector).off('select2:select').on('select2:select', function() {
                const value = $(this).val();
                if (value) {
                    // Force update tampilan
                    $(this).trigger('change');
                    // Pastikan rendered element terlihat
                    setTimeout(function() {
                        const $rendered = $(selector).next('.select2-container').find('.select2-selection__rendered');
                        if ($rendered.length) {
                            $rendered.css({
                                'visibility': 'visible',
                                'opacity': '1',
                                'display': 'block',
                                'color': '#495057'
                            });
                        }
                    }, 50);
                }
            });
            
            // Event handler untuk memastikan nilai tetap terlihat setelah close
            $(selector).off('select2:close').on('select2:close', function() {
                const value = $(this).val();
                if (value) {
                    // Force update tampilan
                    $(this).trigger('change');
                    // Pastikan rendered element terlihat
                    setTimeout(function() {
                        const $rendered = $(selector).next('.select2-container').find('.select2-selection__rendered');
                        if ($rendered.length) {
                            $rendered.css({
                                'visibility': 'visible',
                                'opacity': '1',
                                'display': 'block',
                                'color': '#495057'
                            });
                        }
                    }, 50);
                }
            });
            
            // Event handler untuk memastikan nilai tetap terlihat saat blur
            $(selector).off('blur').on('blur', function() {
                const value = $(this).val();
                if (value && $(this).hasClass('select2-hidden-accessible')) {
                    // Force update tampilan
                    $(this).trigger('change.select2');
                    // Pastikan rendered element terlihat
                    setTimeout(function() {
                        const $rendered = $(selector).next('.select2-container').find('.select2-selection__rendered');
                        if ($rendered.length) {
                            $rendered.css({
                                'visibility': 'visible',
                                'opacity': '1',
                                'display': 'block',
                                'color': '#495057'
                            });
                        }
                    }, 50);
                }
            });
        }

        // Destroy Select2
        function destroySelect2Unit(selector) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
        }
        
        // getUnitSelectTemplate sudah didefinisikan di global scope di atas
        function getUnitSelectTemplateLocal(index, isEdit = false) {
            const btnClass = isEdit ? 'btn-tambah-barang-edit' : 'btn-tambah-barang';
            const btnHapusClass = isEdit ? 'btn-hapus-barang-edit' : 'btn-hapus-barang';
            
            // Clone option dari template tersembunyi
            const $template = $('#template_barang_options');
            const optionsHtml = $template.html();
            
            const qtyBtnClass = isEdit ? 'btn-qty-minus-edit' : 'btn-qty-minus';
            const qtyPlusBtnClass = isEdit ? 'btn-qty-plus-edit' : 'btn-qty-plus';
            const qtyInputClass = isEdit ? 'qty-input-edit' : 'qty-input';
            
            return `
                <div class="unit-select-wrapper mb-2" data-index="${index}">
                    <div class="input-group" style="display: flex; align-items: center;">
                        <select class="form-control select2-unit-dynamic" name="unit[]" data-index="${index}" style="flex: 1;">
                            <option value="">-- Pilih Barang --</option>
                            ${optionsHtml}
                        </select>
                        <div class="qty-control-wrapper" style="display: none;">
                            <button type="button" class="btn-qty ${qtyBtnClass}" data-index="${index}" title="Kurangi Qty">
                                <span style="font-size: 18px;">−</span>
                            </button>
                            <div class="qty-display">
                                <span class="${qtyInputClass}" data-index="${index}">1</span>
                            </div>
                            <button type="button" class="btn-qty ${qtyPlusBtnClass}" data-index="${index}" title="Tambah Qty">
                                <span style="font-size: 18px;">+</span>
                            </button>
                        </div>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-success btn-sm ${btnClass}" title="Tambah Barang" style="display: none;">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                            <button type="button" class="btn btn-danger btn-sm ${btnHapusClass}" title="Hapus" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // initSelect2Dynamic sudah didefinisikan di global scope di atas
        function initSelect2DynamicLocal(selector) {
            if ($(selector).hasClass('select2-hidden-accessible')) {
                $(selector).select2('destroy');
            }
            
            // Tentukan parent modal berdasarkan selector
            let dropdownParent = $(selector).closest('.modal');
            if (dropdownParent.length === 0) {
                dropdownParent = $(document.body);
            }
            
            // Simpan nilai sebelum initialize
            const savedValue = $(selector).val();
            
            $(selector).select2({
                theme: 'bootstrap4',
                placeholder: 'Pilih Barang...',
                allowClear: true,
                width: '100%',
                dropdownParent: dropdownParent,
                language: {
                    noResults: function() {
                        return "Tidak ada hasil";
                    },
                    searching: function() {
                        return "Mencari...";
                    }
                }
            });
            
            // Set nilai kembali setelah initialize
            if (savedValue) {
                $(selector).val(savedValue).trigger('change.select2-dynamic');
            }
            
            // Event handler: ketika user memilih barang, update visibility button dan tampilkan qty control
            $(selector).off('change.select2-dynamic').on('change.select2-dynamic', function() {
                // Update visibility button hapus setelah perubahan
                const $wrapper = $(this).closest('.unit-select-wrapper');
                const isEdit = $wrapper.closest('#edit_unit_pembelian_container').length > 0;
                updateHapusButtonVisibility(isEdit);
                
                // Tampilkan/sembunyikan qty control berdasarkan apakah barang dipilih
                const $qtyControl = $wrapper.find('.qty-control-wrapper');
                if ($(this).val() && $(this).val() !== '') {
                    $qtyControl.show();
                } else {
                    $qtyControl.hide();
                    // Reset qty ke 1
                    $wrapper.find('.qty-input, .qty-input-edit').text('1');
                }
            });
        }
        
        // Tambah select2 baru
        function addUnitSelect(isEdit = false) {
            const containerId = isEdit ? 'edit_unit_pembelian_container' : 'unit_pembelian_container';
            const container = $('#' + containerId);
            const existingSelects = container.find('.unit-select-wrapper');
            const newIndex = existingSelects.length;
            
            // Tambahkan HTML baru
            const newHtml = getUnitSelectTemplate(newIndex, isEdit);
            container.append(newHtml);
            
            // Initialize Select2 pada select baru
            const newSelect = container.find('.unit-select-wrapper[data-index="' + newIndex + '"] select');
            initSelect2Dynamic(newSelect);
            
            // Update visibility button untuk semua select
            updateHapusButtonVisibility(isEdit);
        }
        
        // Hapus select2
        function removeUnitSelect($wrapper, isEdit = false) {
            // Destroy Select2 sebelum hapus
            const $select = $wrapper.find('select');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }
            
            // Hapus wrapper
            $wrapper.remove();
            
            // Update visibility button hapus
            updateHapusButtonVisibility(isEdit);
        }
        
        // Update visibility button hapus
        function updateHapusButtonVisibility(isEdit = false) {
            const containerId = isEdit ? 'edit_unit_pembelian_container' : 'unit_pembelian_container';
            const container = $('#' + containerId);
            const selects = container.find('.unit-select-wrapper');
            
            // Sembunyikan semua button Tambah terlebih dahulu
            container.find('.btn-tambah-barang, .btn-tambah-barang-edit').hide();
            
            selects.each(function(index) {
                const $wrapper = $(this);
                const $select = $wrapper.find('select');
                const $btnTambah = $wrapper.find('.btn-tambah-barang, .btn-tambah-barang-edit');
                const $btnHapus = $wrapper.find('.btn-hapus-barang, .btn-hapus-barang-edit');
                const value = $select.val();
                const isLast = (index === selects.length - 1);
                
                // Tampilkan button hapus jika ada lebih dari 1 select
                if (selects.length > 1) {
                    $btnHapus.show();
                } else {
                    $btnHapus.hide();
                }
                
                // Tampilkan button Tambah hanya di field terakhir
                if (isLast) {
                    $btnTambah.show();
                } else {
                    $btnTambah.hide();
                }
            });
        }
        
        // clearUnitSelects sudah didefinisikan di global scope di atas
        
        // Toggle field unit berdasarkan jenis work order
        // Buat global agar bisa diakses dari luar document.ready
        window.toggleUnitField = function() {
            const jenisWoSelect = $('#id_jenis_wo');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            const unitInput = $('#unit');
            const unitContainer = $('#unit_pembelian_container');
            const unitLabel = $('#label_unit');
            
            // Jika jenis work order adalah "Pembelian", tampilkan container dinamis dan sembunyikan input
            if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                // Hide text input dan unit select (untuk perbaikan)
                unitInput.hide().removeAttr('required').removeAttr('name');
                $('#unit_select').hide().removeAttr('name').removeAttr('required');
                $('#perbaikan_unit_toggle').hide();
                
                unitContainer.show();
                // Ubah label menjadi "Daftar barang"
                unitLabel.html('Daftar barang <span class="text-danger">*</span>');
                
                // Jika container kosong, tambahkan select pertama
                if (unitContainer.find('.unit-select-wrapper').length === 0) {
                    unitContainer.html(getUnitSelectTemplate(0, false));
                    // Gunakan timeout untuk memastikan DOM sudah siap
                    setTimeout(function() {
                        const firstSelect = unitContainer.find('.select2-unit-dynamic[data-index="0"]');
                        if (typeof initSelect2Dynamic === 'function') {
                            initSelect2Dynamic(firstSelect);
                        } else if (typeof window.initSelect2Dynamic === 'function') {
                            window.initSelect2Dynamic(firstSelect);
                        }
                        // Tampilkan button Tambah pada select pertama
                        updateHapusButtonVisibility(false);
                    }, 50);
                } else {
                    // Re-initialize semua select yang ada
                    setTimeout(function() {
                        unitContainer.find('.select2-unit-dynamic').each(function() {
                            if (typeof initSelect2Dynamic === 'function') {
                                initSelect2Dynamic($(this));
                            } else if (typeof window.initSelect2Dynamic === 'function') {
                                window.initSelect2Dynamic($(this));
                            }
                        });
                        // Update visibility button setelah re-initialize
                        updateHapusButtonVisibility(false);
                    }, 50);
                }
            } else if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'perbaikan') {
                // Jika "Perbaikan", sembunyikan container pembelian dan biarkan toggle yang mengatur
                unitContainer.hide();
                // Clear semua select2 dinamis pembelian
                clearUnitSelects(false);
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
                // Note: Toggle perbaikan unit akan dihandle oleh togglePerbaikanUnit function
            } else {
                // Jika bukan "Pembelian" dan bukan "Perbaikan", tampilkan input dan sembunyikan container
                unitInput.show().attr('required', 'required').attr('name', 'unit');
                unitContainer.hide();
                // Clear semua select2 dinamis
                clearUnitSelects(false);
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
            }
        };
        
        // Toggle field unit untuk form edit
        // toggleUnitFieldEdit sudah didefinisikan di global scope di atas
        function toggleUnitFieldEditLocal() {
            const jenisWoSelect = $('#edit_id_jenis_wo');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            const unitInput = $('#edit_unit');
            const unitContainer = $('#edit_unit_pembelian_container');
            const unitLabel = $('#edit_label_unit');
            
            // Jika jenis work order adalah "Pembelian", tampilkan container dinamis dan sembunyikan input
            if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                // Hide text input dan unit select (untuk perbaikan)
                unitInput.hide().removeAttr('required').removeAttr('name');
                $('#edit_unit_select').hide().removeAttr('name').removeAttr('required');
                $('#edit_perbaikan_unit_toggle').hide();
                
                unitContainer.show();
                // Ubah label menjadi "Daftar barang"
                unitLabel.html('Daftar barang <span class="text-danger">*</span>');
                
                // Jika container kosong, tambahkan select pertama
                if (unitContainer.find('.unit-select-wrapper').length === 0) {
                    unitContainer.html(getUnitSelectTemplate(0, true));
                    const firstSelect = unitContainer.find('.select2-unit-dynamic[data-index="0"]');
                    initSelect2Dynamic(firstSelect);
                    // Tampilkan button Tambah pada select pertama
                    updateHapusButtonVisibility(true);
                } else {
                    // Re-initialize semua select yang ada
                    unitContainer.find('.select2-unit-dynamic').each(function() {
                        initSelect2Dynamic($(this));
                    });
                    // Update visibility button setelah re-initialize
                    updateHapusButtonVisibility(true);
                }
            } else if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'perbaikan') {
                // Jika "Perbaikan", sembunyikan container pembelian dan biarkan toggle yang mengatur
                unitContainer.hide();
                // Clear semua select2 dinamis pembelian
                clearUnitSelects(true);
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
                // Note: Toggle perbaikan unit akan dihandle oleh togglePerbaikanUnit function
            } else {
                // Jika bukan "Pembelian" dan bukan "Perbaikan", tampilkan input dan sembunyikan container
                unitInput.show().attr('required', 'required').attr('name', 'unit');
                unitContainer.hide();
                // Clear semua select2 dinamis
                clearUnitSelects(true);
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
            }
        }

        // Filter divisi "Ditujukan" berdasarkan jenis work order
        // Buat global agar bisa diakses dari luar document.ready
        // Filter divisi "Ditujukan" berdasarkan jenis work order - SEDERHANA
        window.filterDivisiDitujukan = function() {
            const jenisWoSelect = $('#id_jenis_wo');
            const ditujukanSelect = $('#ditujukan');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            
            // Hide semua options dengan data-jenis-wo
            ditujukanSelect.find('option[data-jenis-wo]').hide();
            
            // Jika tidak ada jenis WO yang dipilih, disable dropdown
            if (!selectedJenisWo) {
                ditujukanSelect.prop('disabled', true);
                ditujukanSelect.val('');
                ditujukanSelect.find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
                return;
            }
            
            // Untuk Perbaikan: tampilkan opsi Mekanik
            // (tidak ada logika khusus yang memblokir, opsi ditampilkan melalui data-jenis-wo="perbaikan")
            
            // Enable dropdown dan show options sesuai jenis WO
            ditujukanSelect.prop('disabled', false);
            ditujukanSelect.find('option[data-jenis-wo="' + selectedJenisWo.toLowerCase() + '"]').show();
            ditujukanSelect.find('option:first').text('-- Pilih Divisi --');
            
            // Reset value jika yang dipilih tidak sesuai
            const currentValue = ditujukanSelect.val();
            if (currentValue) {
                const currentOption = ditujukanSelect.find('option:selected');
                if (currentOption.length && currentOption.is(':hidden')) {
                    ditujukanSelect.val('');
                }
            }
        }

        // Filter divisi "Ditujukan" untuk form edit - SEDERHANA
        function filterDivisiDitujukanEdit() {
            const jenisWoSelect = $('#edit_id_jenis_wo');
            const ditujukanSelect = $('#edit_ditujukan');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            
            // Hide semua options dengan data-jenis-wo
            ditujukanSelect.find('option[data-jenis-wo]').hide();
            
            // Jika tidak ada jenis WO yang dipilih, disable dropdown
            if (!selectedJenisWo) {
                ditujukanSelect.prop('disabled', true);
                ditujukanSelect.val('');
                ditujukanSelect.find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
                return;
            }
            
            // Untuk Perbaikan: tampilkan opsi Mekanik
            // (tidak ada logika khusus yang memblokir, opsi ditampilkan melalui data-jenis-wo="perbaikan")
            
            // Enable dropdown dan show options sesuai jenis WO
            ditujukanSelect.prop('disabled', false);
            ditujukanSelect.find('option[data-jenis-wo="' + selectedJenisWo.toLowerCase() + '"]').show();
            ditujukanSelect.find('option:first').text('-- Pilih Divisi --');
            
            // Reset value jika yang dipilih tidak sesuai
            const currentValue = ditujukanSelect.val();
            if (currentValue) {
                const currentOption = ditujukanSelect.find('option:selected');
                if (currentOption.length && currentOption.is(':hidden')) {
                    ditujukanSelect.val('');
                }
            }
        };
        
        // Event listener untuk perubahan jenis work order sudah dipindahkan ke luar document.ready
        // dan juga akan ditambahkan di modal saat dibuka
        
        // Event listener untuk perubahan jenis work order di form edit
        $('#edit_id_jenis_wo').on('change', function() {
            const selectedValue = $(this).val();
            const ditujukanSelect = $('#edit_ditujukan');
            
            // Enable field Ditujukan jika Jenis Work Order sudah dipilih
            if (selectedValue && selectedValue !== '') {
                ditujukanSelect.prop('disabled', false).css({
                    'pointer-events': 'auto',
                    'cursor': 'pointer'
                });
                ditujukanSelect.find('option:first').text('-- Pilih Divisi --');
            } else {
                ditujukanSelect.prop('disabled', true);
                ditujukanSelect.val('');
                ditujukanSelect.find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
            }
            
            // Hapus filterDivisiDitujukanEdit karena bisa error jika dipanggil dari luar scope
            toggleUnitFieldEdit();
            // Toggle perbaikan unit untuk edit modal
            if (typeof togglePerbaikanUnit === 'function') {
                togglePerbaikanUnit(true);
            }
        });
        
        // Jalankan filter saat halaman dimuat (jika ada nilai yang sudah dipilih)
        filterDivisiDitujukan();
        toggleUnitField();
        
        // Event handler untuk button tambah barang (form create)
        $(document).on('click', '.btn-tambah-barang', function() {
            addUnitSelect(false);
        });
        
        // Event handler untuk button hapus barang (form create)
        $(document).on('click', '.btn-hapus-barang', function() {
            const $wrapper = $(this).closest('.unit-select-wrapper');
            removeUnitSelect($wrapper, false);
        });
        
        // Event handler untuk button tambah barang (form edit)
        $(document).on('click', '.btn-tambah-barang-edit', function() {
            addUnitSelect(true);
        });
        
        // Event handler untuk button hapus barang (form edit)
        $(document).on('click', '.btn-hapus-barang-edit', function() {
            const $wrapper = $(this).closest('.unit-select-wrapper');
            removeUnitSelect($wrapper, true);
        });
        
        // Event handler untuk button kurang qty (form create)
        $(document).on('click', '.btn-qty-minus', function() {
            const index = $(this).data('index');
            const $qtyDisplay = $(`.qty-input[data-index="${index}"]`);
            let currentQty = parseInt($qtyDisplay.text()) || 1;
            if (currentQty > 1) {
                currentQty--;
                $qtyDisplay.text(currentQty);
            }
        });
        
        // Event handler untuk button tambah qty (form create)
        $(document).on('click', '.btn-qty-plus', function() {
            const index = $(this).data('index');
            const $qtyDisplay = $(`.qty-input[data-index="${index}"]`);
            let currentQty = parseInt($qtyDisplay.text()) || 1;
            currentQty++;
            $qtyDisplay.text(currentQty);
        });
        
        // Event handler untuk button kurang qty (form edit)
        $(document).on('click', '.btn-qty-minus-edit', function() {
            const index = $(this).data('index');
            const $qtyDisplay = $(`.qty-input-edit[data-index="${index}"]`);
            let currentQty = parseInt($qtyDisplay.text()) || 1;
            if (currentQty > 1) {
                currentQty--;
                $qtyDisplay.text(currentQty);
            }
        });
        
        // Event handler untuk button tambah qty (form edit)
        $(document).on('click', '.btn-qty-plus-edit', function() {
            const index = $(this).data('index');
            const $qtyDisplay = $(`.qty-input-edit[data-index="${index}"]`);
            let currentQty = parseInt($qtyDisplay.text()) || 1;
            currentQty++;
            $qtyDisplay.text(currentQty);
        });
        
        // Reset field ditujukan dan unit saat modal create dibuka
        $('#tambahWorkOrderModal').on('show.bs.modal', function() {
            // Set tanggal otomatis ke hari ini
            const today = new Date().toISOString().split('T')[0];
            $('#tanggal').val(today);
            
            $('#id_jenis_wo').val('');
            $('#ditujukan').val('').prop('disabled', true);
            $('#ditujukan').find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
            $('#unit').val('').show().attr('required', 'required').attr('name', 'unit');
            $('#unit_pembelian_container').hide();
            clearUnitSelects(false);
            // Reset label ke default
            $('#label_unit').html('Nama Unit / Code <span class="text-danger">*</span>');
            if (typeof window.filterDivisiDitujukan === 'function') {
                window.filterDivisiDitujukan();
            }
            if (typeof window.toggleUnitField === 'function') {
                window.toggleUnitField();
            }
            
            // Re-bind event handler untuk id_jenis_wo saat modal dibuka
            $('#id_jenis_wo').off('change').on('change', function() {
                const selectedValue = $(this).val();
                const ditujukanSelect = $('#ditujukan');
                
                console.log('Jenis WO changed in modal:', selectedValue);
                
                // Enable field Ditujukan jika Jenis Work Order sudah dipilih
                if (selectedValue && selectedValue !== '') {
                    ditujukanSelect.prop('disabled', false).css({
                        'pointer-events': 'auto',
                        'cursor': 'pointer'
                    });
                    ditujukanSelect.find('option:first').text('-- Pilih Divisi --');
                    console.log('Field ditujukan enabled in modal');
                } else {
                    ditujukanSelect.prop('disabled', true);
                    ditujukanSelect.val('');
                    ditujukanSelect.find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
                }
                
                // Panggil fungsi-fungsi
                if (typeof window.filterDivisiDitujukan === 'function') {
                    window.filterDivisiDitujukan();
                }
                if (typeof window.toggleUnitField === 'function') {
                    window.toggleUnitField();
                }
            });
        });
        
        // Autofocus pada field Jenis Work Order saat modal fully shown
        $('#tambahWorkOrderModal').on('shown.bs.modal', function() {
            // Focus ke field Jenis Work Order
            setTimeout(function() {
                $('#id_jenis_wo').focus();
            }, 300);
        });
        
        // Reset field ditujukan dan unit saat modal edit dibuka
        $('#editWorkOrderModal').on('show.bs.modal', function(e) {
            // Cek apakah sedang dalam proses edit (editWorkOrder sedang populate)
            const isEditing = window.isEditingWorkOrder === true;
            
            if (!isEditing) {
                // Reset hanya jika bukan edit mode
                $('#edit_id_jenis_wo').val('');
                $('#edit_ditujukan').val('').prop('disabled', true);
                $('#edit_ditujukan').find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
                $('#edit_unit').val('').show().attr('required', 'required').attr('name', 'unit');
                $('#edit_unit_pembelian_container').hide();
                clearUnitSelects(true);
                // Reset label ke default
                $('#edit_label_unit').html('Nama Unit / Code <span class="text-danger">*</span>');
                filterDivisiDitujukanEdit();
                toggleUnitFieldEdit();
                // Sembunyikan preview dokumentasi jika bukan edit mode
                $('#previewDokumentasiContainerKadiv').hide();
            }
            // Jangan reset dokumentasi jika sedang dalam proses edit
        });
        
        // Reset checkbox delete dokumentasi saat modal ditutup
        $('#editWorkOrderModal').on('hidden.bs.modal', function() {
        });
        
        // Autofocus pada field Jenis Work Order saat modal edit fully shown
        $('#editWorkOrderModal').on('shown.bs.modal', function() {
            // Focus ke field Jenis Work Order
            setTimeout(function() {
                $('#edit_id_jenis_wo').focus();
            }, 300);
        });

        // Destroy Select2 saat modal ditutup
        $('#tambahWorkOrderModal, #editWorkOrderModal').on('hidden.bs.modal', function() {
            // Destroy semua Select2 dinamis
            $('#unit_pembelian_container, #edit_unit_pembelian_container').find('.select2-unit-dynamic').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            // Reset checkbox delete dokumentasi
        });

        // Pastikan Select2 di-initialize setelah modal fully shown
        $('#tambahWorkOrderModal').on('shown.bs.modal', function() {
            // Re-initialize Select2 dinamis jika jenis work order adalah Pembelian
            const selectedJenisWo = $('#id_jenis_wo').find('option:selected').data('nama-jenis');
            if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                setTimeout(function() {
                    $('#unit_pembelian_container').find('.select2-unit-dynamic').each(function() {
                        initSelect2Dynamic($(this));
                    });
                }, 100);
            }
        });

        $('#editWorkOrderModal').on('shown.bs.modal', function() {
            // Re-initialize Select2 dinamis jika jenis work order adalah Pembelian
            const selectedJenisWo = $('#edit_id_jenis_wo').find('option:selected').data('nama-jenis');
            if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                setTimeout(function() {
                    $('#edit_unit_pembelian_container').find('.select2-unit-dynamic').each(function() {
                        initSelect2Dynamic($(this));
                    });
                }, 100);
            }
        });

        // Event handler untuk button view - pastikan bekerja dengan DataTable
        $(document).off('click', '.btn-view').on('click', '.btn-view', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id');
            if (id && typeof viewWorkOrder === 'function') {
                viewWorkOrder(id);
            }
        });

        // Event handler untuk button edit - pastikan bekerja dengan DataTable
        $(document).off('click', '.btn-edit').on('click', '.btn-edit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (isLockedAction(this)) {
                notifyLockedAction(this, 'Work Order tidak dapat diedit karena status sudah final.');
                return false;
            }

            var id = $(this).data('id');
            if (id && typeof editWorkOrder === 'function') {
                editWorkOrder(id);
            }
            return false;
        });
    }); // Tutup document.ready

    // Event handler di luar document.ready untuk memastikan button view/edit selalu berfungsi
    $(document).on('click', '.btn-view', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        var id = $(this).data('id');
        if (id && typeof viewWorkOrder === 'function') {
            viewWorkOrder(id);
        }
    });

    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        if (isLockedAction(this)) {
            notifyLockedAction(this, 'Work Order tidak dapat diedit karena status sudah final.');
            return false;
        }

        var id = $(this).data('id');
        if (id && typeof editWorkOrder === 'function') {
            editWorkOrder(id);
        }
        return false;
    });

    // Event handler untuk field ditujukan - DI LUAR document.ready agar selalu aktif
    $(document).off('change', '#id_jenis_wo').on('change', '#id_jenis_wo', function() {
        const selectedValue = $(this).val();
        const ditujukanSelect = $('#ditujukan');
        
        console.log('Jenis WO changed:', selectedValue);
        
        // Enable field Ditujukan jika Jenis Work Order sudah dipilih
        if (selectedValue && selectedValue !== '') {
            ditujukanSelect.prop('disabled', false).css({
                'pointer-events': 'auto',
                'cursor': 'pointer'
            });
            ditujukanSelect.find('option:first').text('-- Pilih Divisi --');
            console.log('Field ditujukan enabled');
        } else {
            ditujukanSelect.prop('disabled', true);
            ditujukanSelect.val('');
            ditujukanSelect.find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
        }
        
        // Panggil fungsi-fungsi yang sudah dibuat global
        if (typeof window.filterDivisiDitujukan === 'function') {
            window.filterDivisiDitujukan();
        } else {
            console.error('filterDivisiDitujukan function not found');
        }
        if (typeof window.toggleUnitField === 'function') {
            window.toggleUnitField();
        } else {
            console.error('toggleUnitField function not found');
        }
        // Panggil toggle perbaikan unit untuk menampilkan/menyembunyikan toggle
        if (typeof togglePerbaikanUnit === 'function') {
            togglePerbaikanUnit(false);
        }
    });

    // Form tambah work order - pastikan nilai unit dikirim dengan benar
    $('#tambahWorkOrderForm').on('submit', function(e) {
        const unitInput = $('#unit');
        const unitContainer = $('#unit_pembelian_container');
        
        // Hapus hidden input unit yang mungkin sudah ada
        $('#hidden_unit_field').remove();
        
        // Pastikan semua tab-pane terlihat untuk validasi (temporary)
        const $form = $(this);
        const $tabPanes = $form.find('.tab-pane');
        $tabPanes.css('display', 'block');
        
        // Validasi manual untuk field required
        let isValid = true;
        const requiredFields = $form.find('[required]');
        requiredFields.each(function() {
            const $field = $(this);
            let fieldValue = $field.val();
            
            // Handle select field
            if ($field.is('select')) {
                if (!fieldValue || fieldValue === '') {
                    isValid = false;
                    $field.addClass('is-invalid');
                    
                    // Scroll ke field yang error
                    const $tabPane = $field.closest('.tab-pane');
                    if ($tabPane.length && !$tabPane.hasClass('active')) {
                        const tabId = $tabPane.attr('id');
                        const $tabLink = $form.find('a[href="#' + tabId + '"]');
                        if ($tabLink.length) {
                            $tabLink.tab('show');
                        }
                    }
                } else {
                    $field.removeClass('is-invalid');
                }
            } else {
                // Handle input dan textarea
                if (!fieldValue || (typeof fieldValue === 'string' && fieldValue.trim() === '')) {
                    isValid = false;
                    $field.addClass('is-invalid');
                    
                    // Scroll ke field yang error
                    const $tabPane = $field.closest('.tab-pane');
                    if ($tabPane.length && !$tabPane.hasClass('active')) {
                        const tabId = $tabPane.attr('id');
                        const $tabLink = $form.find('a[href="#' + tabId + '"]');
                        if ($tabLink.length) {
                            $tabLink.tab('show');
                        }
                    }
                } else {
                    $field.removeClass('is-invalid');
                }
            }
        });
        
        // Validasi khusus untuk unit field (pembelian)
        const jenisWo = $('#id_jenis_wo').find('option:selected').data('nama-jenis');
        if (jenisWo && jenisWo.toLowerCase() === 'pembelian') {
            const unitSelects = unitContainer.find('.select2-unit-dynamic');
            if (unitSelects.length === 0 || unitSelects.filter(function() { return $(this).val(); }).length === 0) {
                isValid = false;
                unitContainer.addClass('border border-danger');
                
                // Scroll ke tab detail-wo
                const $tabLink = $form.find('a[href="#detail-wo"]');
                if ($tabLink.length) {
                    $tabLink.tab('show');
                }
            } else {
                unitContainer.removeClass('border border-danger');
            }
        }
        
        // Jika tidak valid, prevent submit
        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
            // Kembalikan display tab-pane
            $tabPanes.not('.active').css('display', 'none');
            $tabPanes.filter('.active').css('display', 'block');
            return false;
        }
        
        // Kembalikan display tab-pane ke normal
        $tabPanes.not('.active').css('display', 'none');
        $tabPanes.filter('.active').css('display', 'block');
        
        // Handle unit field
        if (unitContainer.length && unitContainer.find('.select2-unit-dynamic').length > 0) {
            // Jika container dinamis ada dan memiliki select2, kumpulkan semua nilai
            const unitValues = [];
            unitContainer.find('.select2-unit-dynamic').each(function() {
                const value = $(this).val();
                if (value) {
                    const index = $(this).data('index');
                    const qty = parseInt($(`.qty-input[data-index="${index}"]`).text()) || 1;
                    // Format: "Barang (qty: 5)"
                    unitValues.push(`${value} (qty: ${qty})`);
                }
            });
            
            // Buat hidden input dengan name="unit" yang berisi nilai gabungan
            if (unitValues.length > 0) {
                const unitString = unitValues.join(', ');
                $('<input>').attr({
                    type: 'hidden',
                    id: 'hidden_unit_field',
                    name: 'unit',
                    value: unitString
                }).appendTo($form);
            }
            
            // Hapus name attribute dari semua select2 dinamis
            unitContainer.find('.select2-unit-dynamic').each(function() {
                $(this).removeAttr('name');
            });
            
            // Pastikan input text tidak memiliki name attribute
            if (unitInput.attr('name')) {
                unitInput.removeAttr('name');
            }
        } else if (unitInput.length && unitInput.val()) {
            // Jika input text ada dan memiliki nilai, pastikan dikirim
            if (!unitInput.attr('name')) {
                unitInput.attr('name', 'unit');
            }
            // Pastikan semua select dinamis tidak memiliki name attribute
            unitContainer.find('.select2-unit-dynamic').each(function() {
                $(this).removeAttr('name');
            });
        }
    });

    // Form edit work order - pastikan nilai unit dikirim dengan benar
    $('#editWorkOrderForm').on('submit', function(e) {
        // PENTING: Enable select yang disabled sebelum submit agar nilainya dikirim
        const ditujukanSelect = $('#edit_ditujukan');
        const jenisWoSelect = $('#edit_id_jenis_wo');
        
        // Enable disabled selects before submission
        if (ditujukanSelect.prop('disabled')) {
            ditujukanSelect.prop('disabled', false);
        }
        if (jenisWoSelect.prop('disabled')) {
            jenisWoSelect.prop('disabled', false);
        }
        
        const unitInput = $('#edit_unit');
        const unitContainer = $('#edit_unit_pembelian_container');
        
        // Hapus hidden input unit yang mungkin sudah ada
        $('#hidden_edit_unit_field').remove();
        
        if (unitContainer.is(':visible')) {
            // Jika container dinamis terlihat, kumpulkan semua nilai dari select2
            const unitValues = [];
            unitContainer.find('.select2-unit-dynamic').each(function() {
                const value = $(this).val();
                if (value) {
                    const index = $(this).data('index');
                    const qty = parseInt($(`.qty-input-edit[data-index="${index}"]`).text()) || 1;
                    // Format: "Barang (qty: 5)"
                    unitValues.push(`${value} (qty: ${qty})`);
                }
            });
            
            // Buat hidden input dengan name="unit" yang berisi nilai gabungan
            if (unitValues.length > 0) {
                const unitString = unitValues.join(', ');
                $('<input>').attr({
                    type: 'hidden',
                    id: 'hidden_edit_unit_field',
                    name: 'unit',
                    value: unitString
                }).appendTo($(this));
            }
            
            // Hapus name attribute dari semua select2 dinamis
            unitContainer.find('.select2-unit-dynamic').each(function() {
                $(this).removeAttr('name');
            });
            
            // Pastikan input text tidak memiliki name attribute
            if (unitInput.attr('name')) {
                unitInput.removeAttr('name');
            }
        } else if (unitInput.is(':visible')) {
            // Jika input text terlihat, pastikan nilainya dikirim
            if (!unitInput.attr('name')) {
                unitInput.attr('name', 'unit');
            }
            // Pastikan semua select dinamis tidak memiliki name attribute
            unitContainer.find('.select2-unit-dynamic').each(function() {
                $(this).removeAttr('name');
            });
        }
    });

    // Event handler di luar document.ready untuk memastikan button view/edit selalu berfungsi
    $(document).on('click', '.btn-view', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        var id = $(this).data('id');
        if (id && typeof viewWorkOrder === 'function') {
            viewWorkOrder(id);
        }
    });

    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        if (isLockedAction(this)) {
            notifyLockedAction(this, 'Work Order tidak dapat diedit karena status sudah final.');
            return false;
        }

        var id = $(this).data('id');
        if (id && typeof editWorkOrder === 'function') {
            editWorkOrder(id);
        }
        return false;
    });

    // View work order
    function viewWorkOrder(id) {
        fetch(`/kadivplasma/work-order/${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                $('#view_no_wo').text(data.no_work_order);
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#view_divisi_pengaju').text(data.divisi_pengaju);
                $('#view_ditujukan').text(data.ditujukan);
                
                // Cek jenis work order
                const jenisWo = data.jenis_wo ? data.jenis_wo.toLowerCase() : '';
                const isPembelian = jenisWo === 'pembelian';
                
                // Parse format dengan qty dan tampilkan dalam table
                let unitDisplay = '-';
                let barangTable = '';
                
                if (isPembelian) {
                    // Jika jenis WO adalah Pembelian, tampilkan tabel di div baru
                    if (Array.isArray(data.unit)) {
                        // Jika array, buat table dengan qty
                        if (data.unit.length > 0) {
                            barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                            barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                            data.unit.forEach(function(item, index) {
                                const qtyMatch = item.match(/\(qty:\s*(\d+)\)/);
                                if (qtyMatch) {
                                    const qty = qtyMatch[1];
                                    const barangName = item.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                    barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${barangName}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                                } else {
                                    barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${item}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>-</strong></td></tr>`;
                                }
                            });
                            barangTable += '</tbody></table>';
                        }
                    } else if (data.unit && typeof data.unit === 'string') {
                        // Parse string dengan format "Barang1 (qty: 5), Barang2 (qty: 3)"
                        if (data.unit.includes(',')) {
                            const parts = data.unit.split(',').map(v => v.trim()).filter(v => v);
                            if (parts.length > 0) {
                                barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                                barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                                parts.forEach(function(part, index) {
                                    const qtyMatch = part.match(/\(qty:\s*(\d+)\)/);
                                    if (qtyMatch) {
                                        const qty = qtyMatch[1];
                                        const barangName = part.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                        barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${barangName}</td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                                    } else {
                                        barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${part}</td>></td><td style="width: 25%;" class="text-center"><strong>-</strong></td></tr>`;
                                    }
                                });
                                barangTable += '</tbody></table>';
                            }
                        } else {
                            // Single value dengan atau tanpa qty
                            const qtyMatch = data.unit.match(/\(qty:\s*(\d+)\)/);
                            if (qtyMatch) {
                                const qty = qtyMatch[1];
                                const barangName = data.unit.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                                barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                                barangTable += `<tr><td style="width: 8%;">1</td><td style="width: 42%;">${barangName}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                                barangTable += '</tbody></table>';
                            }
                        }
                    }
                    
                    // Tampilkan tabel di div baru dan sembunyikan field Unit
                    if (barangTable) {
                        $('#view_barang_table').html(barangTable);
                        $('#view_barang_container').show();
                    } else {
                        $('#view_barang_container').hide();
                    }
                    $('#view_label_unit').closest('.form-group').hide();
                } else {
                    // Jika bukan Pembelian, tampilkan di field Unit seperti biasa
                    if (Array.isArray(data.unit)) {
                        if (data.unit.length > 0) {
                            unitDisplay = data.unit.join(', ');
                        }
                    } else if (data.unit && typeof data.unit === 'string') {
                        // Remove qty format jika ada untuk display di field Unit
                        unitDisplay = data.unit.replace(/\s*\(qty:\s*\d+\)/g, '');
                    } else if (data.unit && data.unit.nama_unit) {
                        unitDisplay = data.unit.nama_unit;
                    } else if (data.unit_code) {
                        unitDisplay = data.unit_code;
                    }
                    $('#view_unit').html(unitDisplay);
                    $('#view_label_unit').html('<strong>Unit:</strong>');
                    $('#view_label_unit').closest('.form-group').show();
                    $('#view_barang_container').hide();
                }
                
                // Set status dengan badge berwarna sesuai status
                var statusText = data.status || 'Menunggu';
                var badgeClass = 'badge-secondary';
                if (statusText === 'Disetujui' || statusText === 'Selesai' || statusText === 'Disetujui Atasan' || statusText === 'Diterima Logistik' || statusText === 'Diserahkan ke Divisi' || statusText === 'Dibeli Purchasing' || statusText === 'Dikirim Purchasing') {
                    badgeClass = 'badge-success'; // Hijau untuk status sukses
                } else if (statusText === 'Ditolak' || statusText === 'Ditolak Atasan') {
                    badgeClass = 'badge-danger'; // Merah untuk status ditolak
                } else if (statusText === 'Menunggu' || statusText === 'Menunggu Approval Atasan' || statusText === 'Menunggu Pembelian' || statusText === 'Menunggu Pengiriman') {
                    badgeClass = 'badge-warning'; // Kuning untuk status menunggu
                } else {
                    badgeClass = 'badge-info'; // Biru untuk status lainnya
                }
                $('#view_status').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');
                
                $('#view_jenis_wo').text(data.jenis_wo || '-');
                $('#view_uraian').text(data.uraian);
                // Handle dokumentasi
                if (data.dokumentasi && data.dokumentasi !== '-' && data.dokumentasi.trim() !== '') {
                    // Gunakan dokumentasi_url jika ada, jika tidak gunakan path manual sebagai fallback
                    const dokumentasiUrl = data.dokumentasi_url || ('/storage/' + data.dokumentasi);
                    
                    // Cek apakah file adalah gambar
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    
                    if (isImage) {
                        // Tampilkan button lihat foto
                        $('#view_dokumentasi').html(
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-view-dokumentasi-modal" ' +
                            'data-foto="' + dokumentasiUrl + '" ' +
                            'data-nama="' + (data.no_surat_pengajuan || data.no_work_order || 'Work Order') + '">' +
                            '<i class="fas fa-image"></i> Lihat Foto' +
                            '</button>'
                        );
                    } else {
                        // Untuk file non-gambar (PDF, dll), tampilkan button download
                        $('#view_dokumentasi').html(
                            '<a href="' + dokumentasiUrl + '" target="_blank" class="btn btn-sm btn-outline-primary">' +
                            '<i class="fas fa-file"></i> Lihat Dokumentasi' +
                            '</a>'
                        );
                    }
                } else {
                    $('#view_dokumentasi').html('<span class="text-muted">-</span>');
                }
                $('#viewWorkOrderModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = error.message || 'Gagal mengambil data work order';
                alert('Gagal mengambil data work order: ' + errorMessage);
            });
    }

    // Edit work order
    function editWorkOrder(id) {
        // Set flag untuk skip reset di show.bs.modal
        window.isEditingWorkOrder = true;
        
        // Ambil data dari server
        fetch(`/kadivplasma/work-order/${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                $('#edit_id').val(data.id_surat_pengajuan);
                $('#edit_no_work_order').val(data.no_work_order);
                
                // Format tanggal ke YYYY-MM-DD untuk input type="date"
                let tanggalFormatted = data.tanggal;
                if (data.tanggal && data.tanggal.includes('T')) {
                    // Jika format ISO (2025-12-18T16:00:00.000000Z), ambil hanya bagian tanggal
                    tanggalFormatted = data.tanggal.split('T')[0];
                } else if (data.tanggal && data.tanggal.length > 10) {
                    // Jika ada karakter tambahan, ambil 10 karakter pertama
                    tanggalFormatted = data.tanggal.substring(0, 10);
                }
                $('#edit_tanggal').val(tanggalFormatted);
                $('#edit_divisi_pengaju').val(data.divisi_pengaju);
                $('#edit_id_jenis_wo').val(data.id_jenis_wo).trigger('change');
                
                // Enable field Ditujukan setelah Jenis Work Order dipilih
                if (data.id_jenis_wo) {
                    $('#edit_ditujukan').prop('disabled', false).css({
                        'pointer-events': 'auto',
                        'cursor': 'pointer'
                    });
                    $('#edit_ditujukan').find('option:first').text('-- Pilih Divisi --');
                    
                    // Panggil filter setelah enable
                    setTimeout(function() {
                        if (typeof filterDivisiDitujukanEdit === 'function') {
                            filterDivisiDitujukanEdit();
                        }
                    }, 100);
                }
                
                // Toggle field unit berdasarkan jenis work order
                try {
                    if (typeof toggleUnitFieldEdit === 'function') {
                        toggleUnitFieldEdit();
                    } else if (typeof window.toggleUnitFieldEdit === 'function') {
                        window.toggleUnitFieldEdit();
                    }
                } catch (error) {
                    console.error('Error in toggleUnitFieldEdit:', error);
                }
                
                // Set nilai ditujukan setelah filter diterapkan
                setTimeout(function() {
                    $('#edit_ditujukan').val(data.ditujukan);
                }, 200);
                
                // Toggle section barang berdasarkan jenis WO
                setTimeout(function() {
                    if (typeof toggleBarangSectionEdit === 'function') {
                        toggleBarangSectionEdit();
                    } else if (typeof window.toggleBarangSectionEdit === 'function') {
                        window.toggleBarangSectionEdit();
                    }
                }, 100);
                
                // Set nilai unit ke field yang sesuai
                const selectedJenisWo = $('#edit_id_jenis_wo').find('option:selected').data('nama-jenis');
                
                if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'perbaikan') {
                    // Handle Perbaikan type - check if unit value is from unit list or text
                    const unitValue = data.unit ? (Array.isArray(data.unit) ? data.unit[0] : data.unit.replace(/\s*\(qty:\s*\d+\)/g, '')) : '';
                    
                    // Check if value exists in unit dropdown options
                    const $unitSelect = $('#edit_unit_select');
                    const isUnitFromList = $unitSelect.find('option').filter(function() {
                        return $(this).val() === unitValue;
                    }).length > 0;
                    
                    // Hide pembelian container (tidak perlu untuk Perbaikan)
                    $('#edit_unit_pembelian_container').hide().html('');
                    
                    // Show toggle
                    $('#edit_perbaikan_unit_toggle').show();
                    
                    if (isUnitFromList && unitValue) {
                        // Set toggle to "Unit" and show Select2
                        $('#edit_is_perbaikan_unit_yes').prop('checked', true).parent().addClass('active');
                        $('#edit_is_perbaikan_unit_no').prop('checked', false).parent().removeClass('active');
                        
                        // Hide text input, show select
                        $('#edit_unit').hide().removeAttr('name').removeAttr('required');
                        $unitSelect.show().attr('name', 'unit').attr('required', 'required');
                        
                        // Initialize Select2 and set value
                        setTimeout(function() {
                            // Set value terlebih dahulu
                            $('#edit_unit_select').val(unitValue);
                            
                            // Initialize Select2
                            if (typeof initSelect2UnitDropdown === 'function') {
                                initSelect2UnitDropdown('#edit_unit_select');
                            }
                            
                            // Trigger change setelah initialize
                            setTimeout(function() {
                                $('#edit_unit_select').val(unitValue).trigger('change');
                            }, 50);
                        }, 150);
                    } else {
                        // Set toggle to "Tidak" and show text input
                        $('#edit_is_perbaikan_unit_no').prop('checked', true).parent().addClass('active');
                        $('#edit_is_perbaikan_unit_yes').prop('checked', false).parent().removeClass('active');
                        
                        // Show text input, hide select
                        $('#edit_unit').show().attr('name', 'unit').attr('required', 'required').val(unitValue);
                        $unitSelect.hide().removeAttr('name').removeAttr('required');
                    }
                } else if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                    // Handle multiple values - jika data.unit adalah array atau string yang dipisah koma
                    let unitValues = [];
                    let unitQtys = [];
                    
                    if (Array.isArray(data.unit)) {
                        unitValues = data.unit.filter(u => u);
                        unitQtys = unitValues.map(() => 1); // Default qty 1
                    } else if (typeof data.unit === 'string') {
                        // Parse format: "Barang1 (qty: 5), Barang2 (qty: 3)" atau "Barang1, Barang2"
                        if (data.unit.includes(',')) {
                            const parts = data.unit.split(',').map(v => v.trim());
                            parts.forEach(part => {
                                const qtyMatch = part.match(/\(qty:\s*(\d+)\)/);
                                if (qtyMatch) {
                                    const qty = parseInt(qtyMatch[1]);
                                    const barangName = part.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                    unitValues.push(barangName);
                                    unitQtys.push(qty);
                                } else {
                                    unitValues.push(part);
                                    unitQtys.push(1);
                                }
                            });
                        } else {
                            // Single value
                            const qtyMatch = data.unit.match(/\(qty:\s*(\d+)\)/);
                            if (qtyMatch) {
                                const qty = parseInt(qtyMatch[1]);
                                const barangName = data.unit.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                unitValues.push(barangName);
                                unitQtys.push(qty);
                            } else {
                                unitValues.push(data.unit);
                                unitQtys.push(1);
                            }
                        }
                    } else if (data.unit) {
                        unitValues = [data.unit];
                        unitQtys = [1];
                    }
                    
                    // Clear container terlebih dahulu
                    try {
                        if (typeof window.clearUnitSelects === 'function') {
                            window.clearUnitSelects(true);
                        } else if (typeof clearUnitSelects === 'function') {
                            clearUnitSelects(true);
                        }
                    } catch (error) {
                        console.error('Error clearing unit selects:', error);
                    }
                    const container = $('#edit_unit_pembelian_container');
                    
                    // Buat select2 untuk setiap nilai
                    try {
                        if (unitValues.length > 0) {
                            unitValues.forEach(function(value, index) {
                                try {
                                    const template = typeof getUnitSelectTemplate === 'function' 
                                        ? getUnitSelectTemplate(index, true) 
                                        : '';
                                    if (!template) {
                                        console.error('getUnitSelectTemplate tidak tersedia');
                                        return;
                                    }
                                    
                                    if (index === 0) {
                                        container.html(template);
                                    } else {
                                        container.append(template);
                                    }
                                    
                                    // Set nilai dan initialize Select2
                                    setTimeout(function() {
                                        try {
                                            const $select = container.find('.select2-unit-dynamic[data-index="' + index + '"]');
                                            const $wrapper = $select.closest('.unit-select-wrapper');
                                            
                                            if (typeof initSelect2Dynamic === 'function') {
                                                initSelect2Dynamic($select);
                                            } else if (typeof window.initSelect2Dynamic === 'function') {
                                                window.initSelect2Dynamic($select);
                                            } else {
                                                console.error('initSelect2Dynamic tidak tersedia');
                                                return;
                                            }
                                            
                                            $select.val(value).trigger('change');
                                            
                                            // Set qty
                                            const qty = unitQtys[index] || 1;
                                            $wrapper.find('.qty-input-edit[data-index="' + index + '"]').text(qty);
                                            $wrapper.find('.qty-control-wrapper').show();
                                            
                                            // Tampilkan button Tambah jika ada nilai
                                            if (value) {
                                                $select.closest('.unit-select-wrapper').find('.btn-tambah-barang-edit').show();
                                            }
                                        } catch (error) {
                                            console.error('Error initializing select2:', error);
                                        }
                                    }, 50 * (index + 1));
                                } catch (error) {
                                    console.error('Error creating select template:', error);
                                }
                            });
                            
                            // Update visibility button hapus setelah semua select dibuat
                            setTimeout(function() {
                                try {
                                    if (typeof updateHapusButtonVisibility === 'function') {
                                        updateHapusButtonVisibility(true);
                                    } else if (typeof window.updateHapusButtonVisibility === 'function') {
                                        window.updateHapusButtonVisibility(true);
                                    }
                                } catch (error) {
                                    console.error('Error updating button visibility:', error);
                                }
                            }, 100 * unitValues.length);
                        } else {
                            // Jika tidak ada nilai, buat select kosong
                            const template = typeof getUnitSelectTemplate === 'function' 
                                ? getUnitSelectTemplate(0, true) 
                                : '';
                            if (template) {
                                container.html(template);
                                setTimeout(function() {
                                    try {
                                        const firstSelect = container.find('.select2-unit-dynamic[data-index="0"]');
                                        if (typeof initSelect2Dynamic === 'function') {
                                            initSelect2Dynamic(firstSelect);
                                        } else if (typeof window.initSelect2Dynamic === 'function') {
                                            window.initSelect2Dynamic(firstSelect);
                                        }
                                    } catch (error) {
                                        console.error('Error initializing first select:', error);
                                    }
                                }, 100);
                            }
                        }
                    } catch (error) {
                        console.error('Error creating unit selects:', error);
                    }
                } else {
                    // Jika bukan pembelian dan bukan perbaikan, gunakan text input biasa
                    // Hide toggle
                    $('#edit_perbaikan_unit_toggle').hide();
                    
                    if (Array.isArray(data.unit)) {
                        $('#edit_unit').val(data.unit.join(', '));
                    } else {
                        // Remove qty format jika ada
                        const unitValue = data.unit ? data.unit.replace(/\s*\(qty:\s*\d+\)/g, '') : '';
                        $('#edit_unit').val(unitValue);
                    }
                }
                
                $('#edit_uraian').val(data.uraian);
                
                // Set form action
                $('#editWorkOrderForm').attr('action', `/kadivplasma/work-order/${data.id_surat_pengajuan}`);
                
                // Handle dokumentasi preview - TAMPILKAN SEBELUM MODAL SHOW (seperti purchasing)
                if (data.dokumentasi && data.dokumentasi !== '-' && data.dokumentasi.trim() !== '') {
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    const fileName = data.dokumentasi.split('/').pop();
                    const dokumentasiUrl = data.dokumentasi_url || '/storage/' + data.dokumentasi;
                    
                    // Jika gambar, tampilkan preview
                    if (isImage) {
                        $('#previewDokumentasiKadiv').attr('src', dokumentasiUrl);
                        $('#previewDokumentasiContainerKadiv').show();
                    } else {
                        $('#previewDokumentasiContainerKadiv').hide();
                    }
                } else {
                    $('#previewDokumentasiContainerKadiv').hide();
                }
                
                // Tampilkan modal SETELAH dokumentasi di-set
                $('#editWorkOrderModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMessage = error.message || 'Gagal mengambil data work order';
                alert('Gagal mengambil data work order: ' + errorMessage);
                window.isEditingWorkOrder = false; // Reset flag jika error
            })
            .finally(() => {
                // Reset flag setelah modal shown (delay untuk memastikan modal sudah fully rendered)
                setTimeout(() => {
                    window.isEditingWorkOrder = false;
                }, 500);
            });
    }

    // Delete work order - menggunakan modal konfirmasi
    function deleteWorkOrder(id) {
        const url = `/kadivplasma/work-order/${id}`;
        showDeleteConfirm(url, 'Apakah Anda yakin ingin menghapus work order ini?');
    }

    // View Dokumentasi (dari modal view work order - tutup modal detail dulu, lalu buka modal foto)
    $(document).on('click', '.btn-view-dokumentasi-modal', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fotoUrl = $(this).data('foto');
        const namaWo = $(this).data('nama');
        
        // Set data foto
        $('#dokumentasiViewKadiv').attr('src', fotoUrl);
        $('#dokumentasiViewKadiv').attr('alt', 'Dokumentasi ' + namaWo);
        $('#namaWoViewKadiv').text('Dokumentasi Work Order: ' + namaWo);
        
        // Tutup modal work order terlebih dahulu
        $('#viewWorkOrderModal').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#viewWorkOrderModal').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiKadiv').modal('show');
            // Hapus event listener setelah digunakan
            $('#viewWorkOrderModal').off('hidden.bs.modal');
        });
    });

    // Preview dokumentasi saat edit
    function previewDokumentasiEditKadiv(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileExt = file.name.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
            
            // Sembunyikan file saat ini
            // Jika gambar, tampilkan preview
            if (isImage) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewDokumentasiKadiv').attr('src', e.target.result);
                    $('#previewDokumentasiContainerKadiv').show();
                };
                reader.readAsDataURL(file);
            } else {
                $('#previewDokumentasiContainerKadiv').hide();
            }
        } else {
            // Jika file dihapus, tampilkan kembali file saat ini
            $('#previewDokumentasiContainerKadiv').hide();
        }
    }

</script>
@include('components.delete-confirm-modal')

<!-- Modal View Dokumentasi -->
<div class="modal fade" id="modalViewDokumentasiKadiv" tabindex="-1" role="dialog" aria-labelledby="modalViewDokumentasiKadivLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewDokumentasiKadivLabel">Dokumentasi Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="dokumentasiViewKadiv" src="" alt="Dokumentasi" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0" id="namaWoViewKadiv"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Handler untuk button Kirim Ulang Ajuan
    $(document).on('click', '.btn-resend', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        var id = $(this).data('id');
        var url = $(this).data('url');
        
        if (!id || !url) {
            console.error('ID atau URL tidak ditemukan');
            return false;
        }
        
        // Simpan URL ke global variable untuk digunakan di modal
        window.currentResendUrl = url;
        
        // Tampilkan modal konfirmasi
        $('#konfirmasiKirimUlangModal').modal('show');
        
        return false;
    });

    // Handler untuk button OK di modal konfirmasi kirim ulang
    $(document).on('click', '#btnKonfirmasiKirimUlang', function(e) {
        e.preventDefault();
        
        var url = window.currentResendUrl;
        
        if (!url) {
            console.error('URL tidak ditemukan');
            return false;
        }
        
        // Tutup modal
        $('#konfirmasiKirimUlangModal').modal('hide');
        
        // Kirim AJAX request
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Tampilkan notifikasi sukses
                    if (typeof showToastNotification === 'function') {
                        showToastNotification('success', response.message);
                    }
                    
                    // Redirect atau reload
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    // Tampilkan notifikasi gagal
                    if (typeof showToastNotification === 'function') {
                        showToastNotification('error', response.message);
                    }
                }
            },
            error: function(xhr) {
                var response = xhr.responseJSON;
                var errorMessage = response && response.message ? response.message : 'Terjadi kesalahan saat mengirim work order.';
                
                if (typeof showToastNotification === 'function') {
                    showToastNotification('error', errorMessage);
                }
            }
        });
    });
</script>
@endpush

<!-- Modal Konfirmasi Kirim Ulang Work Order -->
<div class="modal fade" id="konfirmasiKirimUlangModal" tabindex="-1" role="dialog" aria-labelledby="konfirmasiKirimUlangModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 500px;">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1B3C88 !important;">
                <h5 class="modal-title text-white" id="konfirmasiKirimUlangModalLabel">
                    <i class="fas fa-paper-plane mr-2"></i>Konfirmasi Kirim Ulang
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-question-circle" style="font-size: 3.5rem; color: #1B3C88;"></i>
                </div>
                <div style="border-left: 4px solid #1B3C88; background-color: #e8f0fe; border: 1px solid #1B3C88; border-radius: 8px; padding: 20px;">
                    <h6 style="color: #1B3C88; font-weight: 600; margin-bottom: 10px;">
                        <i class="fas fa-info-circle mr-2"></i>Informasi:
                    </h6>
                    <hr style="border-top: 1px solid #1B3C88; margin: 10px 0;">
                    <p style="color: #2c3e50; margin-bottom: 0; font-size: 14px; line-height: 1.6;">
                        Apakah Anda yakin ingin mengirim ulang work order ini ke divisi tujuan?
                    </p>
                </div>
            </div>
            <div class="modal-footer" style="background-color: #f8f9fa;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Batal
                </button>
                <button type="button" class="btn btn-primary" id="btnKonfirmasiKirimUlang" style="background-color: #1B3C88; border-color: #1B3C88;">
                    <i class="fas fa-check mr-2"></i>Ya, Kirim Ulang
                </button>
            </div>
        </div>
    </div>
</div>

@endsection