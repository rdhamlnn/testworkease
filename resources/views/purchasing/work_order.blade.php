@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp
@extends('purchasing.master')

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

    /* Pastikan button action bisa diklik */
    .btn-view,
    .btn-edit,
    .btn-delete {
        cursor: pointer !important;
        position: relative !important;
        z-index: 10 !important;
        pointer-events: auto !important;
    }

    .btn-view img,
    .btn-edit img,
    .btn-delete img {
        pointer-events: none !important;
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

    .btn-icon img {
        width: 16px;
        height: 16px;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('purchasing.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Work Order</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Work Order</h4>
                        <div class="card-header-action">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambahWorkOrderPurchasing">
                                <i class="fas fa-plus"></i> Tambah Work Order
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="purchasingWorkOrderTable">
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
                                    @forelse ($workOrders as $i => $wo)
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
                                            <td>{{ $wo->unit_code ?? $wo->unit }}</td>
                                            <td>{{ Str::limit($wo->uraian, 30) }}</td>
                                            <td>
                                                @if ($status == 'Disetujui' || $status == 'Selesai')
                                                    <span class="badge badge-success">{{ $status }}</span>
                                                @elseif ($status == 'Ditolak')
                                                    <span class="badge badge-danger">{{ $status }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ $status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <button type="button" class="btn btn-info btn-sm btn-icon btn-view" data-id="{{ $wo->id_surat_pengajuan }}" title="Lihat Detail">
                                                        <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" alt="view">
                                                    </button>
                                                    @if($status == 'Menunggu')
                                                    <button type="button"
                                                        class="btn btn-warning btn-sm btn-icon btn-edit"
                                                        data-id="{{ $wo->id_surat_pengajuan }}"
                                                        data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                        data-lock-message="{{ $isLocked ? 'Work Order tidak dapat diedit karena status sudah '.$statusLower.'.' : '' }}"
                                                        title="Edit">
                                                        <img src="https://cdn-icons-png.flaticon.com/128/2355/2355330.png" alt="edit">
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btn-icon btn-delete"
                                                        data-url="{{ route('purchasing.work-order.destroy', $wo->id_surat_pengajuan) }}"
                                                        data-message="Yakin ingin menghapus work order ini?"
                                                        data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                        data-lock-message="{{ $isLocked ? 'Work Order tidak dapat dihapus karena status sudah '.$statusLower.'.' : '' }}"
                                                        title="Hapus">
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

{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambahWorkOrderPurchasing" tabindex="-1" role="dialog" aria-labelledby="modalTambahWorkOrderPurchasingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahWorkOrderPurchasingLabel">Tambah Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="tambahWorkOrderForm" method="POST" action="{{ route('purchasing.work-order.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <!-- Scrollable Tabs Navigation -->
                    <ul class="nav nav-tabs nav-tabs-scrollable" id="tambahWorkOrderTabsPurchasing" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="info-dasar-tab-purchasing" data-toggle="tab" href="#info-dasar-purchasing" role="tab" aria-controls="info-dasar-purchasing" aria-selected="true">
                                <i class="fas fa-info-circle"></i> Informasi Dasar
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="detail-wo-tab-purchasing" data-toggle="tab" href="#detail-wo-purchasing" role="tab" aria-controls="detail-wo-purchasing" aria-selected="false">
                                <i class="fas fa-clipboard-list"></i> Detail Work Order
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="dokumentasi-tab-purchasing" data-toggle="tab" href="#dokumentasi-purchasing" role="tab" aria-controls="dokumentasi-purchasing" aria-selected="false">
                                <i class="fas fa-file-upload"></i> Dokumentasi
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content tab-content-scrollable" id="tambahWorkOrderTabContentPurchasing">
                        <!-- Tab 1: Informasi Dasar -->
                        <div class="tab-pane fade show active" id="info-dasar-purchasing" role="tabpanel" aria-labelledby="info-dasar-tab-purchasing">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="no_work_order">No. Work Order</label>
                                        <input type="text" class="form-control" id="no_work_order" name="no_work_order" readonly value="{{ $nextNoWO }}">
                                        <input type="hidden" name="no_surat_pengajuan" value="{{ $nextNoWO }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('tanggal') is-invalid @enderror" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" required>
                                        @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                        <select class="form-control @error('id_jenis_wo') is-invalid @enderror" name="id_jenis_wo" id="id_jenis_wo" required>
                                            <option value="">-- Pilih Jenis Work Order --</option>
                                            @foreach($jenisWorkOrder as $jenis)
                                                <option value="{{ $jenis->id_jenis_wo }}" data-nama-jenis="{{ $jenis->nama_jenis_wo }}" {{ old('id_jenis_wo') == $jenis->id_jenis_wo ? 'selected' : '' }}>
                                                    {{ $jenis->nama_jenis_wo }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_jenis_wo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 2: Detail Work Order -->
                        <div class="tab-pane fade" id="detail-wo-purchasing" role="tabpanel" aria-labelledby="detail-wo-tab-purchasing">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                        <select class="form-control @error('ditujukan') is-invalid @enderror" name="ditujukan" id="ditujukan" required disabled>
                                            <option value="">-- Pilih Jenis Work Order terlebih dahulu --</option>
                                            @foreach($divisiTujuan as $div)
                                                @if($div->nama_divisi !== 'Administrator' && $div->nama_divisi !== $divisiPengaju)
                                                    <option value="{{ $div->nama_divisi }}" {{ old('ditujukan') === $div->nama_divisi ? 'selected' : '' }}>
                                                        {{ $div->nama_divisi }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('ditujukan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="unit" id="label_unit">Nama Unit / Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" required placeholder="Masukkan unit">
                                        <!-- Container untuk Select2 dinamis (jenis work order Pembelian) -->
                                        <div id="unit_pembelian_container" style="display: none;"></div>
                                        <!-- Template tersembunyi untuk option barang -->
                                        <select id="template_barang_options" style="display: none;">
                                            @if(isset($daftarBarang))
                                                @foreach($daftarBarang as $barang)
                                                    <option value="{{ $barang->nama_barang }}">{{ $barang->nama_barang }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="uraian">Uraian <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('uraian') is-invalid @enderror" id="uraian" name="uraian" rows="4" required placeholder="Masukkan uraian work order">{{ old('uraian') }}</textarea>
                                        @error('uraian')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section Daftar Barang (Optional) -->
                            <div class="row" id="barang-section-purchasing" style="display: none;">
                                <div class="col-md-12">
                                    <hr>
                                    <div class="form-group">
                                        <label>Daftar Barang yang Diperlukan <small class="text-muted">(Opsional - hanya jika diperlukan)</small></label>
                                        <div id="daftar-barang-container-purchasing">
                                            <!-- Barang items akan ditambahkan di sini -->
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary mt-2" id="tambah-barang-btn-purchasing">
                                            <i class="fas fa-plus"></i> Tambah Barang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 3: Dokumentasi -->
                        <div class="tab-pane fade" id="dokumentasi-purchasing" role="tabpanel" aria-labelledby="dokumentasi-tab-purchasing">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="dokumentasi">Dokumentasi</label>
                                        <input type="file" class="form-control @error('dokumentasi') is-invalid @enderror" id="dokumentasi" name="dokumentasi" accept=".pdf,.jpg,.jpeg,.png">
                                        <small class="form-text text-muted">Format: JPG, PNG, PDF. Maksimal 2MB.</small>
                                        @error('dokumentasi')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

{{-- Modal Edit --}}
<div class="modal fade" id="modalEditWorkOrderPurchasing" tabindex="-1" role="dialog" aria-labelledby="modalEditWorkOrderPurchasingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditWorkOrderPurchasingLabel">Edit Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" id="formEditWorkOrderPurchasing" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Scrollable Tabs Navigation -->
                    <ul class="nav nav-tabs nav-tabs-scrollable" id="editWorkOrderTabsPurchasing" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="edit-info-dasar-tab-purchasing" data-toggle="tab" href="#edit-info-dasar-purchasing" role="tab" aria-controls="edit-info-dasar-purchasing" aria-selected="true">
                                <i class="fas fa-info-circle"></i> Informasi Dasar
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="edit-detail-wo-tab-purchasing" data-toggle="tab" href="#edit-detail-wo-purchasing" role="tab" aria-controls="edit-detail-wo-purchasing" aria-selected="false">
                                <i class="fas fa-clipboard-list"></i> Detail Work Order
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="edit-dokumentasi-tab-purchasing" data-toggle="tab" href="#edit-dokumentasi-purchasing" role="tab" aria-controls="edit-dokumentasi-purchasing" aria-selected="false">
                                <i class="fas fa-file-upload"></i> Dokumentasi
                            </a>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content tab-content-scrollable" id="editWorkOrderTabContentPurchasing">
                        <!-- Tab 1: Informasi Dasar -->
                        <div class="tab-pane fade show active" id="edit-info-dasar-purchasing" role="tabpanel" aria-labelledby="edit-info-dasar-tab-purchasing">
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Work Order</label>
                                        <input type="text" class="form-control" id="editNoWorkOrderPurchasing" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="editTanggalPurchasing" name="tanggal" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Divisi Pengaju</label>
                                        <input type="text" class="form-control" value="{{ $divisiPengaju }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Jenis Work Order <span class="text-danger">*</span></label>
                                        <select class="form-control" name="id_jenis_wo" id="editJenisWoPurchasing" required>
                                            <option value="">-- Pilih Jenis --</option>
                                            @foreach($jenisWorkOrder as $jenis)
                                                <option value="{{ $jenis->id_jenis_wo }}" data-nama-jenis="{{ $jenis->nama_jenis_wo }}">{{ $jenis->nama_jenis_wo }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 2: Detail Work Order -->
                        <div class="tab-pane fade" id="edit-detail-wo-purchasing" role="tabpanel" aria-labelledby="edit-detail-wo-tab-purchasing">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Ditujukan <span class="text-danger">*</span></label>
                                        <select class="form-control" name="ditujukan" id="editDitujukanPurchasing" required>
                                            @foreach($divisiTujuan as $div)
                                                <option value="{{ $div->nama_divisi }}">{{ $div->nama_divisi }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label id="edit_label_unit_purchasing">Nama Unit / Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="unit" id="editUnitPurchasing" required placeholder="Masukkan unit">
                                        <!-- Container untuk Select2 dinamis (jenis work order Pembelian) -->
                                        <div id="edit_unit_pembelian_container_purchasing" style="display: none;"></div>
                                        <!-- Template tersembunyi untuk option barang -->
                                        <select id="edit_template_barang_options_purchasing" style="display: none;">
                                            @if(isset($daftarBarang))
                                                @foreach($daftarBarang as $barang)
                                                    <option value="{{ $barang->nama_barang }}">{{ $barang->nama_barang }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Uraian Pekerjaan <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="uraian" id="editUraianPurchasing" rows="4" required></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section Daftar Barang (Optional) -->
                            <div class="row" id="edit-barang-section-purchasing" style="display: none;">
                                <div class="col-md-12">
                                    <hr>
                                    <div class="form-group">
                                        <label>Daftar Barang yang Diperlukan <small class="text-muted">(Opsional - hanya jika diperlukan)</small></label>
                                        <div id="edit-daftar-barang-container-purchasing">
                                            <!-- Barang items akan ditambahkan di sini -->
                                        </div>
                                        <button type="button" class="btn btn-sm btn-primary mt-2" id="edit-tambah-barang-btn-purchasing">
                                            <i class="fas fa-plus"></i> Tambah Barang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tab 3: Dokumentasi -->
                        <div class="tab-pane fade" id="edit-dokumentasi-purchasing" role="tabpanel" aria-labelledby="edit-dokumentasi-tab-purchasing">
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Dokumentasi</label>
                                        <div id="currentDokumentasiPurchasing" class="mb-2" style="display: none;">
                                            <small class="text-muted d-block">File saat ini: <span id="currentDokumentasiNamePurchasing" class="font-weight-bold"></span></small>
                                        </div>
                                        <input type="file" class="form-control" name="dokumentasi" id="editDokumentasiPurchasing" accept=".pdf,.jpg,.jpeg,.png" onchange="previewDokumentasiEditPurchasing(this)">
                                        <small class="form-text text-muted">Format: JPG, PNG, PDF. Maks. 2MB</small>
                                        <div id="previewDokumentasiContainerPurchasing" class="mt-2" style="display: none;">
                                            <img id="previewDokumentasiPurchasing" src="" alt="Preview Dokumentasi" style="max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd;">
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

{{-- Modal View --}}
<div class="modal fade" id="modalViewWorkOrderPurchasing" tabindex="-1" role="dialog" aria-labelledby="modalViewWorkOrderPurchasingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewWorkOrderPurchasingLabel">Detail Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>No. Work Order</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewNoWorkOrderPurchasing"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Tanggal</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewTanggalPurchasing"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Divisi Pengaju</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewDivisiPengajuPurchasing"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Ditujukan</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewDitujukanPurchasing"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Unit</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewUnitPurchasing"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Status</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewStatusPurchasing"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Uraian</strong></label>
                    <p class="form-control-plaintext border p-2 rounded" id="viewUraianPurchasing"></p>
                </div>
                <div class="form-group">
                    <label><strong>Dokumentasi</strong></label>
                    <div id="viewDokumentasiPurchasing" class="form-control-plaintext border p-2 rounded"></div>
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
        $('#purchasingWorkOrderTable').DataTable({
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

        // Counter untuk barang items
        let barangItemCount = 0;
        
        // Toggle section barang berdasarkan jenis WO
        // Filter divisi "Ditujukan" berdasarkan jenis work order
        // Buat global agar bisa diakses dari luar document.ready
        // Untuk Purchasing: Tidak ada jenis Pembelian, Perbaikan → Mekanik, Permintaan → Produksi, Plasma, Quality Control, Mekanik
        window.filterDivisiDitujukan = function() {
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
            // Untuk Purchasing: Tidak ada jenis Pembelian
            let allowedDivisi = [];
            
            if (selectedJenisWo.toLowerCase() === 'perbaikan') {
                allowedDivisi = ['Mekanik'];
            } else if (selectedJenisWo.toLowerCase() === 'permintaan') {
                allowedDivisi = ['Produksi', 'Plasma', 'Quality Control', 'Mekanik'];
            }
            // Pembelian tidak berlaku untuk purchasing (purchasing tidak bisa mengajukan pembelian)
            
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
        };
        
        // Buat global agar bisa diakses dari luar document.ready
        window.toggleBarangSection = function() {
            // SELALU hide karena sudah diwakili oleh Select2 dinamis (unit_pembelian_container)
            $('#barang-section-purchasing').hide();
            $('#daftar-barang-container-purchasing').empty();
            barangItemCount = 0;
        }
        
        // Toggle section barang untuk form edit
        function toggleBarangSectionEdit() {
            // SELALU hide karena sudah diwakili oleh Select2 dinamis (edit_unit_pembelian_container_purchasing)
            $('#edit-barang-section-purchasing').hide();
            $('#edit-daftar-barang-container-purchasing').empty();
        }
        
        // Toggle field unit berdasarkan jenis work order
        // Buat global agar bisa diakses dari luar document.ready
        window.toggleUnitField = function() {
            const jenisWoSelect = $('#id_jenis_wo');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            const unitInput = $('#unit');
            const unitContainer = $('#unit_pembelian_container');
            const unitLabel = $('#label_unit');
            
            // Jika jenis work order adalah "Pembelian" atau "Perbaikan", tampilkan container dinamis
            if (selectedJenisWo && (selectedJenisWo.toLowerCase() === 'pembelian' || selectedJenisWo.toLowerCase() === 'perbaikan')) {
                unitInput.hide().removeAttr('required').removeAttr('name');
                unitContainer.show();
                
                // Untuk Pembelian: wajib, untuk Perbaikan: opsional
                if (selectedJenisWo.toLowerCase() === 'pembelian') {
                    unitLabel.html('Daftar barang <span class="text-danger">*</span>');
                } else {
                    unitLabel.html('Daftar barang <small class="text-muted">(Opsional)</small>');
                }
            } else {
                // Jika bukan "Pembelian" atau "Perbaikan", tampilkan input dan sembunyikan container
                unitInput.show().attr('required', 'required').attr('name', 'unit');
                unitContainer.hide();
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
            }
        }
        
        // Filter divisi "Ditujukan" untuk form edit
        function filterDivisiDitujukanEdit() {
            const jenisWoSelect = $('#editJenisWoPurchasing');
            const ditujukanSelect = $('#editDitujukanPurchasing');
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
            
            // Aturan: Untuk Purchasing: Tidak ada jenis Pembelian, Perbaikan → Mekanik, Permintaan → Produksi, Plasma, Quality Control, Mekanik
            let allowedDivisi = [];
            
            if (selectedJenisWo.toLowerCase() === 'perbaikan') {
                allowedDivisi = ['Mekanik'];
            } else if (selectedJenisWo.toLowerCase() === 'permintaan') {
                allowedDivisi = ['Produksi', 'Plasma', 'Quality Control', 'Mekanik'];
            }
            // Pembelian tidak berlaku untuk purchasing (purchasing tidak bisa mengajukan pembelian)
            
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
        
        // Toggle field unit untuk form edit
        function toggleUnitFieldEdit() {
            const jenisWoSelect = $('#editJenisWoPurchasing');
            const selectedJenisWo = jenisWoSelect.find('option:selected').data('nama-jenis');
            const unitInput = $('#editUnitPurchasing');
            const unitContainer = $('#edit_unit_pembelian_container_purchasing');
            const unitLabel = $('#edit_label_unit_purchasing');
            
            // Jika jenis work order adalah "Pembelian" atau "Perbaikan", tampilkan container dinamis
            if (selectedJenisWo && (selectedJenisWo.toLowerCase() === 'pembelian' || selectedJenisWo.toLowerCase() === 'perbaikan')) {
                unitInput.hide().removeAttr('required').removeAttr('name');
                unitContainer.show();
                
                // Untuk Pembelian: wajib, untuk Perbaikan: opsional
                if (selectedJenisWo.toLowerCase() === 'pembelian') {
                    unitLabel.html('Daftar barang <span class="text-danger">*</span>');
                } else {
                    unitLabel.html('Daftar barang <small class="text-muted">(Opsional)</small>');
                }
            } else {
                // Jika bukan "Pembelian" atau "Perbaikan", tampilkan input dan sembunyikan container
                unitInput.show().attr('required', 'required').attr('name', 'unit');
                unitContainer.hide();
                // Ubah label kembali menjadi "Nama Unit / Code"
                unitLabel.html('Nama Unit / Code <span class="text-danger">*</span>');
            }
        }
        
        // Tambah item barang
        $('#tambah-barang-btn-purchasing').on('click', function() {
            const html = `
                <div class="row mb-2 barang-item" data-index="${barangItemCount}">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="barang[${barangItemCount}][nama_barang]" 
                               placeholder="Nama Barang" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="barang[${barangItemCount}][jumlah]" 
                               placeholder="Jumlah" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="barang[${barangItemCount}][satuan]" 
                               placeholder="Satuan (pcs/kg)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="barang[${barangItemCount}][estimasi_harga]" 
                               placeholder="Estimasi Harga" min="0" step="0.01">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-barang" title="Hapus">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#daftar-barang-container-purchasing').append(html);
            barangItemCount++;
        });
        
        // Hapus item barang
        $(document).on('click', '.btn-remove-barang', function() {
            $(this).closest('.barang-item').remove();
        });
        
        // Tambah item barang untuk form edit
        $('#edit-tambah-barang-btn-purchasing').on('click', function() {
            const currentCount = $('#edit-daftar-barang-container-purchasing .barang-item').length;
            const html = `
                <div class="row mb-2 barang-item" data-index="${currentCount}">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="barang[${currentCount}][nama_barang]" 
                               placeholder="Nama Barang" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" class="form-control" name="barang[${currentCount}][jumlah]" 
                               placeholder="Jumlah" min="1" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control" name="barang[${currentCount}][satuan]" 
                               placeholder="Satuan (pcs/kg)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" class="form-control" name="barang[${currentCount}][estimasi_harga]" 
                               placeholder="Estimasi Harga" min="0" step="0.01">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm btn-remove-barang-edit" title="Hapus">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            $('#edit-daftar-barang-container-purchasing').append(html);
        });
        
        // Hapus item barang untuk form edit
        $(document).on('click', '.btn-remove-barang-edit', function() {
            $(this).closest('.barang-item').remove();
        });
        
        // Event listener untuk perubahan jenis work order sudah dipindahkan ke luar document.ready
        // untuk memastikan bekerja meskipun modal dibuka setelah page load
        
        // Event listener untuk perubahan jenis work order di form edit
        $('#editJenisWoPurchasing').on('change', function() {
            const selectedValue = $(this).val();
            const ditujukanSelect = $('#editDitujukanPurchasing');
            
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
            toggleBarangSectionEdit();
            toggleUnitFieldEdit();
        });
        
        // Jalankan toggle saat halaman dimuat
        if (typeof window.toggleBarangSection === 'function') {
            window.toggleBarangSection();
        }
        if (typeof window.toggleUnitField === 'function') {
            window.toggleUnitField();
        }
        if (typeof window.filterDivisiDitujukan === 'function') {
            window.filterDivisiDitujukan();
        }
        
        // Reset field ditujukan dan unit saat modal create dibuka
        $('#modalTambahWorkOrderPurchasing').on('show.bs.modal', function() {
            // Set tanggal otomatis ke hari ini
            const today = new Date().toISOString().split('T')[0];
            $('#tanggal').val(today);
            
            $('#id_jenis_wo').val('');
            $('#ditujukan').val('').prop('disabled', true);
            $('#ditujukan').find('option:first').text('-- Pilih Jenis Work Order terlebih dahulu --');
            $('#unit').val('').show().attr('required', 'required').attr('name', 'unit');
            $('#unit_pembelian_container').hide();
            // Kembalikan label ke default
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
                if (typeof window.toggleBarangSection === 'function') {
                    window.toggleBarangSection();
                }
            });
        });

        // Event handler untuk button view
        $(document).on('click', '.btn-view', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            var id = $(this).data('id');
            if (id && typeof viewWorkOrderPurchasing === 'function') {
                viewWorkOrderPurchasing(id);
            }
        });

        // Event handler untuk button edit
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            if (isLockedAction(this)) {
                notifyLockedAction(this, 'Work Order tidak dapat diedit karena status sudah final.');
                return false;
            }

            var id = $(this).data('id');
            if (id && typeof editWorkOrderPurchasing === 'function') {
                editWorkOrderPurchasing(id);
            }
            return false;
        });
    });

    // Event handler di luar document.ready untuk memastikan button view/edit selalu berfungsi
    $(document).on('click', '.btn-view', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        var id = $(this).data('id');
        if (id && typeof viewWorkOrderPurchasing === 'function') {
            viewWorkOrderPurchasing(id);
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
        if (id && typeof editWorkOrderPurchasing === 'function') {
            editWorkOrderPurchasing(id);
        }
        return false;
    });

    // Event handler untuk field ditujukan - DI LUAR document.ready agar selalu aktif
    // Gunakan event delegation untuk memastikan bekerja meskipun modal dibuka setelah page load
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
            console.log('Field ditujukan disabled');
        }
        
        // Panggil fungsi-fungsi yang sudah dibuat global dengan delay kecil untuk memastikan tersedia
        setTimeout(function() {
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
            if (typeof window.toggleBarangSection === 'function') {
                window.toggleBarangSection();
            }
        }, 50);
    });

    // Form tambah work order - biarkan submit normal ke server
    $('#tambahWorkOrderForm').on('submit', function() {
        // Form akan submit ke server secara normal
        // Server akan handle validasi dan redirect
    });

    // Form edit work order - biarkan submit normal ke server
    $('#formEditWorkOrderPurchasing').on('submit', function() {
        // Form akan submit ke server secara normal
        // Server akan handle validasi dan redirect
    });

    // View work order
    function viewWorkOrderPurchasing(id) {
        // Ambil data dari server
        const WORK_ORDER_API_URL_PUR = "{{ route('purchasing.api.work-order', ['id' => '__ID__']) }}";
        fetch(WORK_ORDER_API_URL_PUR.replace('__ID__', id))
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                $('#viewNoWorkOrderPurchasing').text(data.no_work_order || data.no_surat_pengajuan);
                $('#viewTanggalPurchasing').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#viewDivisiPengajuPurchasing').text(data.divisi_pengaju);
                $('#viewDitujukanPurchasing').text(data.ditujukan);
                // Fix unit display - show unit name if available, otherwise show unit string
                let unitDisplay = '-';
                if (data.unit && typeof data.unit === 'string') {
                    unitDisplay = data.unit;
                } else if (data.unit && data.unit.nama_unit) {
                    unitDisplay = data.unit.nama_unit;
                } else if (data.unit_code) {
                    unitDisplay = data.unit_code;
                }
                $('#viewUnitPurchasing').text(unitDisplay);
                
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
                $('#viewStatusPurchasing').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');
                
                $('#viewUraianPurchasing').text(data.uraian);
                if (data.dokumentasi && data.dokumentasi !== '-') {
                    // Cek apakah file adalah gambar
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    
                    if (isImage) {
                        // Tampilkan button lihat foto
                        $('#viewDokumentasiPurchasing').html(
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-view-dokumentasi-modal" ' +
                            'data-foto="/storage/' + data.dokumentasi + '" ' +
                            'data-nama="' + (data.no_work_order || data.no_surat_pengajuan) + '">' +
                            '<i class="fas fa-image"></i> Lihat Foto' +
                            '</button>'
                        );
                    } else {
                        // Untuk file non-gambar, tampilkan button download
                        $('#viewDokumentasiPurchasing').html(
                            '<a href="/storage/' + data.dokumentasi + '" target="_blank" class="btn btn-sm btn-outline-primary">' +
                            '<i class="fas fa-file"></i> Lihat Dokumentasi' +
                            '</a>'
                        );
                    }
                } else {
                    $('#viewDokumentasiPurchasing').html('<span class="text-muted">-</span>');
                }
                $('#modalViewWorkOrderPurchasing').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data work order');
            });
    }

    // Edit work order
    function editWorkOrderPurchasing(id) {
        // Ambil data dari server
        const WORK_ORDER_API_URL_PUR = "{{ route('purchasing.api.work-order', ['id' => '__ID__']) }}";
        const UPDATE_WORK_ORDER_URL_PUR = "{{ route('purchasing.work-order.update', ['id' => '__ID__']) }}";
        fetch(WORK_ORDER_API_URL_PUR.replace('__ID__', id))
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                $('#formEditWorkOrderPurchasing').attr('action', UPDATE_WORK_ORDER_URL_PUR.replace('__ID__', data.id_surat_pengajuan));
                $('#editNoWorkOrderPurchasing').val(data.no_work_order || data.no_surat_pengajuan);
                $('#editTanggalPurchasing').val(data.tanggal);
                $('#editDitujukanPurchasing').val(data.ditujukan);
                $('#editJenisWoPurchasing').val(data.id_jenis_wo);
                
                // Enable field Ditujukan setelah Jenis Work Order dipilih
                if (data.id_jenis_wo) {
                    $('#editDitujukanPurchasing').prop('disabled', false);
                    $('#editDitujukanPurchasing').find('option:first').text('-- Pilih Divisi --');
                }
                
                // Toggle field ditujukan dan unit berdasarkan jenis work order
                filterDivisiDitujukanEdit();
                toggleUnitFieldEdit();
                
                // Set nilai ditujukan setelah filter dijalankan
                $('#editDitujukanPurchasing').val(data.ditujukan);
                $('#editUnitPurchasing').val(data.unit);
                $('#editUraianPurchasing').val(data.uraian);
                
                // Toggle section barang berdasarkan jenis WO
                setTimeout(function() {
                    toggleBarangSectionEdit();
                }, 100);
                
                // Handle dokumentasi preview
                if (data.dokumentasi && data.dokumentasi !== '-') {
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    const fileName = data.dokumentasi.split('/').pop();
                    
                    // Tampilkan nama file saat ini
                    $('#currentDokumentasiNamePurchasing').text(fileName);
                    $('#currentDokumentasiPurchasing').show();
                    
                    // Jika gambar, tampilkan preview
                    if (isImage) {
                        $('#previewDokumentasiPurchasing').attr('src', '/storage/' + data.dokumentasi);
                        $('#previewDokumentasiContainerPurchasing').show();
                    } else {
                        $('#previewDokumentasiContainerPurchasing').hide();
                    }
                } else {
                    $('#currentDokumentasiPurchasing').hide();
                    $('#previewDokumentasiContainerPurchasing').hide();
                }
                
                $('#modalEditWorkOrderPurchasing').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data work order');
            });
    }

    // Preview dokumentasi saat edit
    function previewDokumentasiEditPurchasing(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileExt = file.name.split('.').pop().toLowerCase();
            const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
            
            // Sembunyikan file saat ini
            $('#currentDokumentasiPurchasing').hide();
            
            // Jika gambar, tampilkan preview
            if (isImage) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#previewDokumentasiPurchasing').attr('src', e.target.result);
                    $('#previewDokumentasiContainerPurchasing').show();
                };
                reader.readAsDataURL(file);
            } else {
                $('#previewDokumentasiContainerPurchasing').hide();
            }
        } else {
            // Jika file dihapus, tampilkan kembali file saat ini
            const currentFile = $('#currentDokumentasiNamePurchasing').text();
            if (currentFile) {
                $('#currentDokumentasiPurchasing').show();
            }
            $('#previewDokumentasiContainerPurchasing').hide();
        }
    }

    // View Dokumentasi (dari modal view work order - tutup modal detail dulu, lalu buka modal foto)
    $(document).on('click', '.btn-view-dokumentasi-modal', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fotoUrl = $(this).data('foto');
        const namaWo = $(this).data('nama');
        
        // Set data foto
        $('#dokumentasiViewPurchasing').attr('src', fotoUrl);
        $('#dokumentasiViewPurchasing').attr('alt', 'Dokumentasi ' + namaWo);
        $('#namaWoViewPurchasing').text('Dokumentasi Work Order: ' + namaWo);
        
        // Tutup modal work order terlebih dahulu
        $('#modalViewWorkOrderPurchasing').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#modalViewWorkOrderPurchasing').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiPurchasing').modal('show');
            // Hapus event listener setelah digunakan
            $('#modalViewWorkOrderPurchasing').off('hidden.bs.modal');
        });
    });

</script>

<!-- Modal View Dokumentasi -->
<div class="modal fade" id="modalViewDokumentasiPurchasing" tabindex="-1" role="dialog" aria-labelledby="modalViewDokumentasiPurchasingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewDokumentasiPurchasingLabel">Dokumentasi Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="dokumentasiViewPurchasing" src="" alt="Dokumentasi" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0" id="namaWoViewPurchasing"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@include('components.delete-confirm-modal')
@endsection

