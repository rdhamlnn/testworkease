@extends('kadivqc.master')

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
        transition: all 0.3s ease;
    }
    
    .nav-tabs-scrollable .nav-link:hover {
        border-color: #e9ecef #e9ecef #dee2e6;
        background-color: #e9ecef;
        color: #1B3C88;
    }
    
    .nav-tabs-scrollable .nav-link.active {
        color: #fff;
        background-color: #1B3C88;
        border-color: #1B3C88 #1B3C88 transparent;
        font-weight: 600;
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
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('kadivqc.dashboard') }}">Dashboard</a></div>
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
                                <th>Ditujukan</th>
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
                                    <td>{{ $wo->no_work_order }}</td>
                                    <td>
                                        @if($wo->jenisWorkOrder)
                                            {{ $wo->jenisWorkOrder->nama_jenis_wo }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $wo->divisi_pengaju }}</td>
                                    <td>{{ $wo->ditujukan }}</td>
                                    <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                    <td>{{ $wo->unit_code }}</td>
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
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" class="btn btn-info btn-sm btn-icon btn-view" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" title="Lihat Detail">
                                                <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" alt="view">
                                            </button>
                                            @if($status == 'Menunggu')
                                            <button type="button" class="btn btn-warning btn-sm btn-icon btn-edit" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" title="Edit"
                                                data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                data-lock-message="{{ $isLocked ? 'Work Order tidak dapat diedit karena status sudah '.$statusLower.'.' : '' }}">
                                                <img src="https://cdn-icons-png.flaticon.com/128/2355/2355330.png" alt="edit">
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-icon btn-delete" 
                                                data-url="{{ route('kadivqc.hapus-work-order', $wo->id_surat_pengajuan) }}"
                                                data-message="Yakin ingin menghapus work order ini?" title="Hapus"
                                                data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                data-lock-message="{{ $isLocked ? 'Work Order tidak dapat dihapus karena status sudah '.$statusLower.'.' : '' }}">
                                                <img src="https://cdn-icons-png.flaticon.com/128/484/484611.png" alt="hapus">
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
            <form id="tambahWorkOrderForm" action="{{ route('kadivqc.work-order.store') }}" method="POST" enctype="multipart/form-data">
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
                                        <input type="text" class="form-control" id="no_work_order" name="no_work_order" readonly value="{{ $nextWorkOrderNumber ?? '01/QC/KCE/2025' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Divisi Pengaju <span class="text-danger">*</span></label>
                                        @php($divisiPengaju = Auth::user()->divisi->nama_divisi ?? session('divisi') ?? 'Quality Control')
                                        <input type="text" class="form-control" value="{{ $divisiPengaju }}" readonly>
                                        <input type="hidden" name="divisi_pengaju" value="{{ $divisiPengaju }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="ditujukan" id="ditujukan" required disabled>
                                            <option value="">-- Pilih Jenis Work Order terlebih dahulu --</option>
                                            @foreach($divisi as $d)
                                                @if($d->nama_divisi !== 'Administrator' && $d->nama_divisi !== $divisiPengaju)
                                                    <option value="{{ $d->nama_divisi }}">{{ $d->nama_divisi }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Pilih Jenis Work Order terlebih dahulu untuk mengaktifkan field ini</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 2: Detail Work Order -->
                        <div class="tab-pane fade" id="detail-wo" role="tabpanel" aria-labelledby="detail-wo-tab">
                            <div class="row mt-3">
                                <div class="col-md-12">
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
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="unit" id="label_unit">Nama Unit / Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="unit" name="unit" required placeholder="Masukkan unit">
                                        <!-- Container untuk Select2 dinamis (jenis work order Pembelian) -->
                                        <div id="unit_pembelian_container" style="display: none;"></div>
                                        <!-- Template tersembunyi untuk option barang -->
                                        <select id="template_barang_options" style="display: none;">
                                            @foreach($daftarBarang as $barang)
                                                <option value="{{ $barang->nama_barang }}">{{ $barang->nama_barang }}</option>
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
                    <div class="row">
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
                                <input type="text" class="form-control" id="edit_divisi_pengaju" name="divisi_pengaju" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                <select class="form-control" name="ditujukan" id="edit_ditujukan" required disabled>
                                    <option value="">-- Pilih Jenis Work Order terlebih dahulu --</option>
                                    @foreach($divisi as $d)
                                        @php($divisiPengaju = Auth::user()->divisi->nama_divisi ?? session('divisi') ?? 'Quality Control')
                                        @if($d->nama_divisi !== 'Administrator' && $d->nama_divisi !== $divisiPengaju)
                                            <option value="{{ $d->nama_divisi }}">{{ $d->nama_divisi }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Pilih Jenis Work Order terlebih dahulu untuk mengaktifkan field ini</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unit" id="label_edit_unit">Nama Unit / Code <span class="text-danger">*</span></label>
                                <!-- Input text untuk jenis work order selain Pembelian -->
                                <input type="text" class="form-control" id="edit_unit" name="unit" required>
                                <!-- Container untuk Select2 dinamis (jenis work order Pembelian) -->
                                <div id="edit_unit_pembelian_container" style="display: none;"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_dokumentasi">Dokumentasi</label>
                                <div id="currentDokumentasiKadiv" class="mb-2" style="display: none;">
                                    <small class="text-muted d-block">File saat ini: <span id="currentDokumentasiNameKadiv" class="font-weight-bold"></span></small>
                                </div>
                                <input type="file" class="form-control" id="edit_dokumentasi" name="dokumentasi" accept=".pdf,.jpg,.jpeg,.png" onchange="previewDokumentasiEditKadiv(this)">
                                <small class="form-text text-muted">Format: JPG, PNG Maks. 2MB</small>
                                <div id="previewDokumentasiContainerKadiv" class="mt-2" style="display: none;">
                                    <img id="previewDokumentasiKadiv" src="" alt="Preview Dokumentasi" style="max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_uraian">Uraian <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_uraian" name="uraian" rows="3" required></textarea>
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
                            <label id="view_label_unit"><strong>Unit:</strong></label>
                            <p id="view_unit" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Status:</strong></label>
                            <p id="view_status" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
        
        // Template HTML untuk select2 dinamis
        function getUnitSelectTemplate(index, isEdit = false) {
            const btnClass = isEdit ? 'btn-tambah-barang-edit' : 'btn-tambah-barang';
            const btnHapusClass = isEdit ? 'btn-hapus-barang-edit' : 'btn-hapus-barang';
            
            // Clone option dari template tersembunyi
            const $template = $('#template_barang_options');
            const optionsHtml = $template.html();
            
            return `
                <div class="unit-select-wrapper mb-2" data-index="${index}">
                    <div class="input-group">
                        <select class="form-control select2-unit-dynamic" name="unit[]" data-index="${index}">
                            <option value="">-- Pilih Barang --</option>
                            ${optionsHtml}
                        </select>
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
        
        // Inisialisasi Select2 untuk select2 dinamis (single select)
        function initSelect2Dynamic(selector) {
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
            
            // Event handler: ketika user memilih barang, update visibility button
            $(selector).off('change.select2-dynamic').on('change.select2-dynamic', function() {
                // Update visibility button hapus setelah perubahan
                const $wrapper = $(this).closest('.unit-select-wrapper');
                const isEdit = $wrapper.closest('#edit_unit_pembelian_container').length > 0;
                updateHapusButtonVisibility(isEdit);
            });
            
            // Set nilai kembali setelah initialize
            if (savedValue) {
                $(selector).val(savedValue).trigger('change.select2-dynamic');
            }
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
        
        // Clear semua select2 dinamis
        function clearUnitSelects(isEdit = false) {
            const containerId = isEdit ? 'edit_unit_pembelian_container' : 'unit_pembelian_container';
            const container = $('#' + containerId);
            
            // Destroy semua Select2
            container.find('.select2-unit-dynamic').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
            
            // Clear container
            container.empty();
        }
        
        // Toggle field unit berdasarkan jenis work order
        function toggleUnitField() {
            const jenisWoSelect = $('#id_jenis_wo');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            const unitInput = $('#unit');
            const unitContainer = $('#unit_pembelian_container');
            const unitLabel = $('#label_unit');
            
            // Jika jenis work order adalah "Pembelian", tampilkan container dinamis dan sembunyikan input
            if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                unitInput.hide().removeAttr('required').removeAttr('name');
                unitContainer.show();
                // Ubah label menjadi "Daftar barang"
                unitLabel.html('Daftar barang <span class="text-danger">*</span>');
                
                // Jika container kosong, tambahkan select pertama
                if (unitContainer.find('.unit-select-wrapper').length === 0) {
                    unitContainer.html(getUnitSelectTemplate(0, false));
                    const firstSelect = unitContainer.find('.select2-unit-dynamic[data-index="0"]');
                    initSelect2Dynamic(firstSelect);
                } else {
                    // Re-initialize semua select yang ada
                    unitContainer.find('.select2-unit-dynamic').each(function() {
                        initSelect2Dynamic($(this));
                    });
                }
            } else {
                // Jika bukan "Pembelian", tampilkan input dan sembunyikan container
                unitInput.show().attr('required', 'required').attr('name', 'unit');
                unitContainer.hide();
                // Clear semua select2 dinamis
                clearUnitSelects(false);
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
            }
        }
        
        // Toggle field unit untuk form edit
        function toggleUnitFieldEdit() {
            const jenisWoSelect = $('#edit_id_jenis_wo');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            const unitInput = $('#edit_unit');
            const unitContainer = $('#edit_unit_pembelian_container');
            const unitLabel = $('#label_edit_unit');
            
            // Jika jenis work order adalah "Pembelian", tampilkan container dinamis dan sembunyikan input
            if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                unitInput.hide().removeAttr('required').removeAttr('name');
                unitContainer.show();
                // Ubah label menjadi "Daftar barang"
                unitLabel.html('Daftar barang <span class="text-danger">*</span>');
                
                // Jika container kosong, tambahkan select pertama
                if (unitContainer.find('.unit-select-wrapper').length === 0) {
                    unitContainer.html(getUnitSelectTemplate(0, true));
                    const firstSelect = unitContainer.find('.select2-unit-dynamic[data-index="0"]');
                    initSelect2Dynamic(firstSelect);
                } else {
                    // Re-initialize semua select yang ada
                    unitContainer.find('.select2-unit-dynamic').each(function() {
                        initSelect2Dynamic($(this));
                    });
                }
            } else {
                // Jika bukan "Pembelian", tampilkan input dan sembunyikan container
                unitInput.show().attr('required', 'required').attr('name', 'unit');
                unitContainer.hide();
                // Clear semua select2 dinamis
                clearUnitSelects(true);
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
            }
        }

        // Filter divisi "Ditujukan" berdasarkan jenis work order
        function filterDivisiDitujukan() {
            const jenisWoSelect = $('#id_jenis_wo');
            const ditujukanSelect = $('#ditujukan');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            
            // Simpan semua option divisi terlebih dahulu
            if (!ditujukanSelect.data('original-options')) {
                ditujukanSelect.data('original-options', ditujukanSelect.html());
            }
            
            // Reset ke semua option
            ditujukanSelect.html(ditujukanSelect.data('original-options'));
            
            // Jika tidak ada jenis WO yang dipilih, tampilkan semua
            if (!selectedJenisWo) {
                return;
            }
            
            // Filter berdasarkan jenis work order
            let allowedDivisi = [];
            
            if (selectedJenisWo.toLowerCase() === 'pembelian') {
                allowedDivisi = ['Logistik'];
            } else if (selectedJenisWo.toLowerCase() === 'perbaikan') {
                allowedDivisi = ['Mekanik'];
            } else if (selectedJenisWo.toLowerCase() === 'permintaan') {
                allowedDivisi = ['Produksi', 'Plasma'];
            }
            
            // Jika ada filter, sembunyikan option yang tidak sesuai
            if (allowedDivisi.length > 0) {
                ditujukanSelect.find('option').each(function() {
                    const optionValue = $(this).val();
                    const optionText = $(this).text().trim();
                    
                    // Selalu tampilkan option placeholder
                    if (optionValue === '') {
                        return;
                    }
                    
                    // Cek apakah divisi ini termasuk dalam allowedDivisi
                    const isAllowed = allowedDivisi.some(divisi => 
                        optionText.toLowerCase() === divisi.toLowerCase() || 
                        optionValue.toLowerCase() === divisi.toLowerCase()
                    );
                    
                    if (!isAllowed) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
                
                // Reset pilihan jika yang dipilih tidak sesuai filter
                const currentValue = ditujukanSelect.val();
                if (currentValue && !allowedDivisi.some(divisi => 
                    currentValue.toLowerCase() === divisi.toLowerCase()
                )) {
                    ditujukanSelect.val('');
                }
            }
        }
        
        // Filter divisi "Ditujukan" untuk form edit
        function filterDivisiDitujukanEdit() {
            const jenisWoSelect = $('#edit_id_jenis_wo');
            const ditujukanSelect = $('#edit_ditujukan');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            
            // Simpan semua option divisi terlebih dahulu
            if (!ditujukanSelect.data('original-options')) {
                ditujukanSelect.data('original-options', ditujukanSelect.html());
            }
            
            // Reset ke semua option
            ditujukanSelect.html(ditujukanSelect.data('original-options'));
            
            // Jika tidak ada jenis WO yang dipilih, tampilkan semua
            if (!selectedJenisWo) {
                return;
            }
            
            // Filter berdasarkan jenis work order
            let allowedDivisi = [];
            
            if (selectedJenisWo.toLowerCase() === 'pembelian') {
                allowedDivisi = ['Logistik'];
            } else if (selectedJenisWo.toLowerCase() === 'perbaikan') {
                allowedDivisi = ['Mekanik'];
            } else if (selectedJenisWo.toLowerCase() === 'permintaan') {
                allowedDivisi = ['Produksi', 'Plasma'];
            }
            
            // Jika ada filter, sembunyikan option yang tidak sesuai
            if (allowedDivisi.length > 0) {
                ditujukanSelect.find('option').each(function() {
                    const optionValue = $(this).val();
                    const optionText = $(this).text().trim();
                    
                    // Selalu tampilkan option placeholder
                    if (optionValue === '') {
                        return;
                    }
                    
                    // Cek apakah divisi ini termasuk dalam allowedDivisi
                    const isAllowed = allowedDivisi.some(divisi => 
                        optionText.toLowerCase() === divisi.toLowerCase() || 
                        optionValue.toLowerCase() === divisi.toLowerCase()
                    );
                    
                    if (!isAllowed) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
                
                // Reset pilihan jika yang dipilih tidak sesuai filter
                const currentValue = ditujukanSelect.val();
                if (currentValue && !allowedDivisi.some(divisi => 
                    currentValue.toLowerCase() === divisi.toLowerCase()
                )) {
                    ditujukanSelect.val('');
                }
            }
        }
        
        // Event listener untuk perubahan jenis work order
        $('#id_jenis_wo').on('change', function() {
            const selectedValue = $(this).val();
            const ditujukanSelect = $('#ditujukan');
            
            // Enable field Ditujukan jika Jenis Work Order sudah dipilih
            if (selectedValue && selectedValue !== '') {
                ditujukanSelect.prop('disabled', false);
                ditujukanSelect.find('option:first').text('-- Pilih Divisi --');
            } else {
                ditujukanSelect.prop('disabled', true);
                ditujukanSelect.val('');
                ditujukanSelect.find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
            }
            
            filterDivisiDitujukan();
            toggleUnitField();
        });
        
        // Event listener untuk perubahan jenis work order di form edit
        $('#edit_id_jenis_wo').on('change', function() {
            const selectedValue = $(this).val();
            const ditujukanSelect = $('#edit_ditujukan');
            
            // Enable field Ditujukan jika Jenis Work Order sudah dipilih
            if (selectedValue && selectedValue !== '') {
                ditujukanSelect.prop('disabled', false);
                ditujukanSelect.find('option:first').text('-- Pilih Divisi --');
            } else {
                ditujukanSelect.prop('disabled', true);
                ditujukanSelect.val('');
                ditujukanSelect.find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
            }
            
            filterDivisiDitujukanEdit();
            toggleUnitFieldEdit();
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
        
        // Reset field ditujukan dan unit saat modal create dibuka
        $('#tambahWorkOrderModal').on('show.bs.modal', function() {
            $('#id_jenis_wo').val('');
            $('#ditujukan').val('').prop('disabled', true);
            $('#ditujukan').find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
            $('#unit').val('').show().attr('required', 'required').attr('name', 'unit');
            $('#unit_pembelian_container').hide();
            clearUnitSelects(false);
            // Kembalikan label ke default
            $('#label_unit').html('Nama Unit / Code <span class="text-danger">*</span>');
            filterDivisiDitujukan();
            toggleUnitField();
        });
        
        // Reset field ditujukan dan unit saat modal edit dibuka
        $('#editWorkOrderModal').on('show.bs.modal', function() {
            $('#edit_id_jenis_wo').val('');
            $('#edit_ditujukan').val('').prop('disabled', true);
            $('#edit_ditujukan').find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
            $('#edit_unit').val('').show().attr('required', 'required').attr('name', 'unit');
            $('#edit_unit_pembelian_container').hide();
            clearUnitSelects(true);
            // Kembalikan label ke default
            $('#label_edit_unit').html('Nama Unit / Code <span class="text-danger">*</span>');
            filterDivisiDitujukanEdit();
            toggleUnitFieldEdit();
        });

        // Destroy Select2 saat modal ditutup
        $('#tambahWorkOrderModal, #editWorkOrderModal').on('hidden.bs.modal', function() {
            // Destroy semua Select2 dinamis
            $('#unit_pembelian_container, #edit_unit_pembelian_container').find('.select2-unit-dynamic').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });
        });

        // Pastikan Select2 di-initialize setelah modal fully shown
        $('#tambahWorkOrderModal').on('shown.bs.modal', function() {
            // Focus ke field Jenis Work Order
            setTimeout(function() {
                $('#id_jenis_wo').focus();
            }, 300);
            
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
            // Focus ke field Jenis Work Order
            setTimeout(function() {
                $('#edit_id_jenis_wo').focus();
            }, 300);
            
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

        // Event handler untuk button view
        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            viewWorkOrder(id);
        });

        // Event handler untuk button edit
        $(document).on('click', '.btn-edit', function(e) {
            if (isLockedAction(this)) {
                e.preventDefault();
                e.stopPropagation();
                notifyLockedAction(this, 'Work Order tidak dapat diedit karena status sudah final.');
                return;
            }

            var id = $(this).data('id');
            editWorkOrder(id);
        });
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
                    unitValues.push(value);
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
                    unitValues.push(value);
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

    // View work order
    function viewWorkOrder(id) {
        // Ambil data dari server
        fetch(`/kadivqc/work-order/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#view_no_wo').text(data.no_work_order);
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#view_divisi_pengaju').text(data.divisi_pengaju);
                $('#view_ditujukan').text(data.ditujukan);
                
                // Ubah label berdasarkan jenis work order
                const jenisWo = data.jenis_wo || '';
                if (jenisWo.toLowerCase() === 'pembelian') {
                    $('#view_label_unit').html('<strong>Barang:</strong>');
                } else {
                    $('#view_label_unit').html('<strong>Unit:</strong>');
                }
                
                // Fix unit display - show unit name if available, otherwise show unit string
                let unitDisplay = '-';
                if (Array.isArray(data.unit)) {
                    // Jika array, gabungkan dengan koma
                    unitDisplay = data.unit.filter(u => u).join(', ');
                } else if (data.unit && typeof data.unit === 'string') {
                    unitDisplay = data.unit;
                } else if (data.unit && data.unit.nama_unit) {
                    unitDisplay = data.unit.nama_unit;
                } else if (data.unit_code) {
                    unitDisplay = data.unit_code;
                }
                $('#view_unit').text(unitDisplay);
                
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
                            'data-nama="' + (data.no_work_order || data.no_surat_pengajuan) + '">' +
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
                alert('Gagal mengambil data work order');
            });
    }

    // Edit work order
    function editWorkOrder(id) {
        // Ambil data dari server
        fetch(`/kadivqc/work-order/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#edit_id').val(data.id_surat_pengajuan);
                $('#edit_no_work_order').val(data.no_work_order);
                $('#edit_tanggal').val(data.tanggal);
                $('#edit_divisi_pengaju').val(data.divisi_pengaju);
                $('#edit_id_jenis_wo').val(data.id_jenis_wo);
                
                // Enable field Ditujukan setelah Jenis Work Order dipilih
                if (data.id_jenis_wo) {
                    $('#edit_ditujukan').prop('disabled', false);
                    $('#edit_ditujukan').find('option:first').text('-- Pilih Divisi --');
                }
                
                // Toggle field ditujukan dan unit berdasarkan jenis work order
                filterDivisiDitujukanEdit();
                toggleUnitFieldEdit();
                
                // Set nilai ditujukan setelah filter dijalankan
                $('#edit_ditujukan').val(data.ditujukan);
                
                // Set nilai unit ke field yang sesuai
                const selectedJenisWo = $('#edit_id_jenis_wo').find('option:selected').data('nama-jenis');
                if (selectedJenisWo && selectedJenisWo.toLowerCase() === 'pembelian') {
                    // Handle multiple values - jika data.unit adalah array atau string yang dipisah koma
                    let unitValues = [];
                    if (Array.isArray(data.unit)) {
                        unitValues = data.unit.filter(u => u);
                    } else if (typeof data.unit === 'string' && data.unit.includes(',')) {
                        unitValues = data.unit.split(',').map(v => v.trim()).filter(v => v);
                    } else if (data.unit) {
                        unitValues = [data.unit];
                    }
                    
                    // Clear container terlebih dahulu
                    clearUnitSelects(true);
                    const container = $('#edit_unit_pembelian_container');
                    
                    // Buat select2 untuk setiap nilai
                    if (unitValues.length > 0) {
                        unitValues.forEach(function(value, index) {
                            if (index === 0) {
                                // Select pertama
                                container.html(getUnitSelectTemplate(0, true));
                            } else {
                                // Select tambahan
                                container.append(getUnitSelectTemplate(index, true));
                            }
                            
                            // Set nilai dan initialize Select2
                            setTimeout(function() {
                                const $select = container.find('.select2-unit-dynamic[data-index="' + index + '"]');
                                initSelect2Dynamic($select);
                                $select.val(value).trigger('change.select2-dynamic');
                                
                                // Tampilkan button Tambah jika ada nilai
                                if (value) {
                                    $select.closest('.unit-select-wrapper').find('.btn-tambah-barang-edit').show();
                                }
                            }, 50 * (index + 1));
                        });
                        
                        // Update visibility button hapus setelah semua select dibuat
                        setTimeout(function() {
                            updateHapusButtonVisibility(true);
                        }, 100 * unitValues.length);
                    } else {
                        // Jika tidak ada nilai, buat select kosong
                        container.html(getUnitSelectTemplate(0, true));
                        setTimeout(function() {
                            const firstSelect = container.find('.select2-unit-dynamic[data-index="0"]');
                            initSelect2Dynamic(firstSelect);
                        }, 100);
                    }
                } else {
                    // Jika bukan pembelian, gunakan nilai pertama jika array
                    if (Array.isArray(data.unit)) {
                        $('#edit_unit').val(data.unit.join(', '));
                    } else {
                        $('#edit_unit').val(data.unit);
                    }
                }
                
                $('#edit_uraian').val(data.uraian);
                
                // Handle dokumentasi preview
                if (data.dokumentasi && data.dokumentasi !== '-') {
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    const fileName = data.dokumentasi.split('/').pop();
                    
                    // Tampilkan nama file saat ini
                    $('#currentDokumentasiNameKadiv').text(fileName);
                    $('#currentDokumentasiKadiv').show();
                    
                    // Jika gambar, tampilkan preview
                    if (isImage) {
                        $('#previewDokumentasiKadiv').attr('src', '/storage/' + data.dokumentasi);
                        $('#previewDokumentasiContainerKadiv').show();
                    } else {
                        $('#previewDokumentasiContainerKadiv').hide();
                    }
                } else {
                    $('#currentDokumentasiKadiv').hide();
                    $('#previewDokumentasiContainerKadiv').hide();
                }
                
                // Set form action
                $('#editWorkOrderForm').attr('action', `/kadivqc/work-order/${data.id_surat_pengajuan}`);
                
                $('#editWorkOrderModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data work order');
            });
    }

    // Delete work order - menggunakan modal konfirmasi
    function deleteWorkOrder(id) {
        const url = `/kadivqc/work-order/${id}`;
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
            $('#currentDokumentasiKadiv').hide();
            
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
            const currentFile = $('#currentDokumentasiNameKadiv').text();
            if (currentFile) {
                $('#currentDokumentasiKadiv').show();
            }
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
@endsection