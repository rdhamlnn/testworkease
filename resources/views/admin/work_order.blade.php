@extends('admin.master')

@section('title', 'Work Order')

@section('styles')
<style>
    .table th,
    .table td {
        text-align: left !important;
        vertical-align: middle;
        padding: 10px 12px !important; /* PERBAIKAN: Kurangi padding sedikit */
    }

    .modal-header {
        background-color: #1B3C88;
        color: #fff;
    }

    .modal-header .close {
        color: #fff;
        opacity: 1;
    }

    .modal-header .close:hover {
        opacity: 0.8;
    }

    .modal-body {
        padding: 20px 30px;
    }

    .modal-footer {
        padding: 15px 30px;
    }

    .badge-success {
        background-color: #10b981;
    }

    .badge-danger {
        background-color: #dc2626;
    }

    .badge-warning {
        background-color: #f59e0b;
    }

    .badge-info {
        background-color: #3b82f6;
    }

    /* Container table-responsive untuk scroll horizontal - PERBAIKAN */
    .table-responsive {
        display: block !important;
        width: 100%;
        overflow-x: visible !important; /* PERBAIKAN: Hapus overflow-x, biarkan card-body yang handle */
        -webkit-overflow-scrolling: touch;
        -ms-overflow-style: -ms-autohiding-scrollbar;
    }

    /* Pastikan tabel fleksibel - PERBAIKAN */
    .table {
        width: 100% !important;
        min-width: 1000px !important;
        table-layout: auto;
        border-collapse: collapse !important; /* PERBAIKAN: Pastikan border collapse */
    }

    /* PERBAIKAN: Hapus width yang terlalu ketat, gunakan min-width saja */
    .table th:nth-child(1),
    .table td:nth-child(1) {
        min-width: 60px;
    }
    .table th:nth-child(2),
    .table td:nth-child(2) {
        min-width: 140px;
    }
    .table th:nth-child(3),
    .table td:nth-child(3) {
        min-width: 130px;
    }
    .table th:nth-child(4),
    .table td:nth-child(4) {
        min-width: 130px;
    }
    .table th:nth-child(5),
    .table td:nth-child(5) {
        min-width: 110px;
    }
    .table th:nth-child(6),
    .table td:nth-child(6) {
        min-width: 160px;
    }
    .table th:nth-child(7),
    .table td:nth-child(7) {
        min-width: 140px;
    }
    .table th:nth-child(8),
    .table td:nth-child(8) {
        min-width: 200px;
    }
    .table th:nth-child(9),
    .table td:nth-child(9) {
        min-width: 130px;
    }
    .table th:nth-child(10),
    .table td:nth-child(10) {
        min-width: 110px;
    }

    /* Kolom aksi - PASTIKAN TIDAK WRAP */
    .table th:last-child,
    .table td:last-child {
        white-space: nowrap !important;
        min-width: 180px !important;
        padding: 10px 8px !important;
    }

    .action-buttons {
        display: flex !important;
        align-items: center;
        justify-content: flex-start;
        gap: 5px;
        flex-wrap: nowrap !important;
        min-height: 32px;
        white-space: nowrap !important;
    }

    .action-buttons .btn {
        margin: 0;
        padding: 4px 8px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        flex-shrink: 0 !important;
        white-space: nowrap !important;
    }

    .action-buttons .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Button Action - Simple & Clean dengan Font Awesome */
    .btn-view {
        min-width: 32px;
        padding: 4px 8px;
    }

    .btn-view i {
        font-size: 14px;
    }

    .btn-view:hover {
        background-color: #138496 !important;
        border-color: #117a8b !important;
    }

    .btn-icon i {
        font-size: 14px;
        flex-shrink: 0;
    }

    /* Pastikan td aksi memiliki vertical alignment yang benar */
    .table td:last-child {
        vertical-align: middle !important;
    }

    /* PERBAIKAN: Fix DataTables scrollX yang menyebabkan baris kosong */
    .dataTables_scrollHead,
    .dataTables_scrollBody {
        border-collapse: collapse !important;
    }

    .dataTables_scrollHead thead th,
    .dataTables_scrollBody tbody td {
        padding: 10px 12px !important;
    }

    /* PERBAIKAN: Hilangkan spacing yang tidak perlu dari DataTables */
    .dataTables_wrapper .dataTables_scroll {
        border: none !important;
    }

    .dataTables_wrapper .dataTables_scrollHead {
        margin-bottom: 0 !important;
    }

    .dataTables_wrapper .dataTables_scrollBody {
        margin-top: 0 !important;
    }

    /* PERBAIKAN: Pastikan tidak ada gap antara header dan body */
    .dataTables_scrollHeadInner {
        width: 100% !important;
    }

    .dataTables_scrollHeadInner table {
        margin-bottom: 0 !important;
        border-bottom: none !important;
    }

    .dataTables_scrollBody table {
        margin-top: 0 !important;
        border-top: none !important;
    }

    /* PERBAIKAN: Pastikan pagination dan info ikut scroll horizontal */
    .card-body {
        overflow-x: auto !important;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    /* PERBAIKAN: Hanya terapkan min-width pada elemen DataTables, bukan semua child */
    .card-body > .table-responsive {
        min-width: 1000px !important;
    }

    .dataTables_wrapper {
        width: 100% !important;
        min-width: 1000px !important;
        overflow-x: visible;
        display: block !important;
    }

    /* PERBAIKAN: Pastikan SEMUA elemen di dalam wrapper memiliki min-width yang sama */
    .dataTables_wrapper > * {
        min-width: 1000px !important;
    }

    /* PERBAIKAN: Row untuk controls (length, filter) - Pastikan ikut scroll */
    .dataTables_wrapper > .row:first-child,
    .dataTables_wrapper .row:first-of-type {
        margin: 0 !important;
        width: 100% !important;
        min-width: 1000px !important;
        max-width: none !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
    }

    /* PERBAIKAN: Row untuk info dan pagination - Pastikan ikut scroll */
    .dataTables_wrapper > .row:last-child,
    .dataTables_wrapper .row:last-of-type {
        margin: 0 !important;
        width: 100% !important;
        min-width: 1000px !important;
        max-width: none !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
    }

    /* PERBAIKAN: Semua row di dalam dataTables_wrapper */
    .dataTables_wrapper .row {
        margin: 0 !important;
        width: 100% !important;
        min-width: 1000px !important;
        max-width: none !important;
        display: flex !important;
        flex-wrap: nowrap !important;
    }

    /* PERBAIKAN: Kolom-kolom di dalam row - PENTING */
    .dataTables_wrapper .row > div[class*="col"] {
        flex-shrink: 0 !important;
        min-width: auto !important;
        max-width: none !important;
    }

    /* PERBAIKAN: Kolom yang berisi filter harus di pojok kanan - LEBIH SPESIFIK */
    .dataTables_wrapper .row > div[class*="col-md-6"]:last-child,
    .dataTables_wrapper .row > div[class*="col-sm-12"]:last-child,
    .dataTables_wrapper .row > div:last-child {
        text-align: right !important;
        display: block !important;
    }

    /* PERBAIKAN: Filter di dalam kolom terakhir */
    .dataTables_wrapper .row > div:last-child .dataTables_filter {
        display: inline-block !important;
        text-align: right !important;
        margin-left: auto !important;
        float: right !important;
    }

    .dataTables_wrapper .row > div {
        flex-shrink: 0 !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        white-space: nowrap !important;
    }

    /* PERBAIKAN: Fix length menu agar angka terlihat - LEBIH SPESIFIK */
    .dataTables_wrapper .dataTables_length {
        white-space: nowrap !important;
        min-width: 220px !important;
        width: auto !important;
        display: inline-block !important;
        position: relative;
        z-index: 1;
    }

    .dataTables_wrapper .dataTables_length label {
        white-space: nowrap !important;
        display: inline-flex !important;
        align-items: center;
        width: auto !important;
        margin: 0 !important;
    }

    .dataTables_wrapper .dataTables_length select {
        display: inline-block !important;
        width: auto !important;
        min-width: 70px !important;
        max-width: 100px !important;
        margin: 0 5px !important;
        padding: 0.375rem 1.75rem 0.375rem 0.75rem !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* PERBAIKAN: Fix info section agar angka terlihat */
    .dataTables_wrapper .dataTables_info {
        padding-top: 0.85em;
        white-space: nowrap !important;
        min-width: 280px !important;
        display: inline-block !important;
        width: auto !important;
    }

    /* PERBAIKAN: Fix pagination agar ikut scroll - LEBIH SPESIFIK */
    .dataTables_wrapper .dataTables_paginate {
        margin: 0 !important;
        white-space: nowrap !important;
        text-align: right !important;
        float: right !important;
        min-width: 250px !important;
        width: auto !important;
        display: inline-block !important;
    }

    .dataTables_wrapper .dataTables_paginate .pagination {
        margin: 0 !important;
        white-space: nowrap !important;
        display: flex !important;
        flex-wrap: nowrap !important;
    }

    /* PERBAIKAN: Filter cari ikut scroll ke pojok kanan - PENTING - LEBIH SPESIFIK */
    .dataTables_wrapper .dataTables_filter {
        white-space: nowrap !important;
        min-width: 200px !important;
        width: auto !important;
        display: inline-block !important;
        text-align: right !important;
        margin-left: auto !important;
        flex-shrink: 0 !important;
    }

    .dataTables_wrapper .dataTables_filter label {
        white-space: nowrap !important;
        display: inline-flex !important;
        align-items: center;
        width: auto !important;
        margin: 0 !important;
        justify-content: flex-end !important;
    }

    .dataTables_wrapper .dataTables_filter input {
        display: inline-block !important;
        width: auto !important;
        min-width: 150px !important;
        margin-left: 5px !important;
    }

    /* PERBAIKAN: Pastikan table-responsive juga memiliki min-width */
    .table-responsive {
        min-width: 1000px !important;
    }

    /* PERBAIKAN: Pastikan kolom Bootstrap tidak menyusut */
    .dataTables_wrapper .row > div[class*="col-sm"],
    .dataTables_wrapper .row > div[class*="col-md"],
    .dataTables_wrapper .row > div[class*="col-lg"] {
        flex-shrink: 0 !important;
        min-width: auto !important;
        max-width: none !important;
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

    /* View Work Order Modal Styles */
    #modalViewWorkOrder .modal-body {
        max-height: 80vh;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding: 20px 30px;
        scrollbar-width: thin;
        scrollbar-color: #1B3C88 #f1f1f1;
    }
    
    #modalViewWorkOrder .modal-body::-webkit-scrollbar {
        width: 10px;
        -webkit-appearance: none;
    }
    
    #modalViewWorkOrder .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 5px;
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.1);
    }
    
    #modalViewWorkOrder .modal-body::-webkit-scrollbar-thumb {
        background: #1B3C88;
        border-radius: 5px;
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
    }
    
    #modalViewWorkOrder .modal-body::-webkit-scrollbar-thumb:hover {
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
    
    #view_barang_table table th,
    #view_barang_table table td {
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
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Work Order</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Flash Message Sukses --}}
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <!-- Work Order List -->
        <div class="card">
            <div class="card-header">
                <h4>Semua Work Order</h4>
                <div class="card-header-action">
                    {{-- Tombol Buat Work Order dihapus - Admin hanya dapat memantau dan mengelola --}}
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="workOrderTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>No Work-Order</th>
                                <th>Divisi Pengaju</th>
                                <th>Jenis WO</th>
                                <th>Hari/Tanggal</th>
                                <th>Unit/Code</th>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                                <th>Total Harga</th>
                                <th>Uraian</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($workOrders as $i => $wo)
                                @php
                                    $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                                    $jenisWo = $wo->jenisWorkOrder ? strtolower($wo->jenisWorkOrder->nama_jenis_wo) : '';
                                    $isPembelian = $jenisWo === 'pembelian';
                                    $isPermintaan = $jenisWo === 'permintaan';
                                    $isPerbaikan = $jenisWo === 'perbaikan';
                                    
                                    // Parse unit field untuk mendapatkan barang, qty, dan satuan
                                    $barangItems = [];
                                    $qtyItems = [];
                                    $satuanItems = [];
                                    $calculatedTotalHarga = 0;
                                    
                                    // Buat array lookup untuk satuan dari master barang (jika tersedia)
                                    $barangLookup = isset($daftarBarang) ? collect($daftarBarang)->keyBy('nama_barang') : collect();
                                    
                                    if ($isPembelian && isset($wo->unit) && $wo->unit && $wo->unit !== '-') {
                                        $parts = explode(', ', $wo->unit);
                                        foreach ($parts as $part) {
                                            if (preg_match('/^(.+?)\s*\(qty:\s*(\d+)\)$/i', trim($part), $matches)) {
                                                $namaBarang = trim($matches[1]);
                                                $qty = (int)$matches[2];
                                                $barangItems[] = $namaBarang;
                                                $qtyItems[] = $qty;
                                                $masterBarang = $barangLookup->get($namaBarang);
                                                $satuanItems[] = $masterBarang->satuan ?? '-';
                                                if ($masterBarang && $masterBarang->harga_barang > 0) {
                                                    $calculatedTotalHarga += $masterBarang->harga_barang * $qty;
                                                }
                                            } elseif (!empty(trim($part))) {
                                                $namaBarang = trim($part);
                                                $barangItems[] = $namaBarang;
                                                $qtyItems[] = 1;
                                                $masterBarang = $barangLookup->get($namaBarang);
                                                $satuanItems[] = $masterBarang->satuan ?? '-';
                                                if ($masterBarang && $masterBarang->harga_barang > 0) {
                                                    $calculatedTotalHarga += $masterBarang->harga_barang;
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td data-order="{{ $i + 1 }}">{{ $i + 1 }}</td>
                                    <td>{{ $wo->no_work_order }}</td>
                                    <td>{{ $wo->divisi_pengaju }}</td>
                                    <td>{{ $wo->jenisWorkOrder->nama_jenis_wo ?? '-' }}</td>
                                    <td data-order="{{ \Carbon\Carbon::parse($wo->tanggal)->format('Ymd') }}">{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                    {{-- Kolom Unit/Code --}}
                                    <td>
                                        @if($isPembelian)
                                            <span class="text-muted">-</span>
                                        @elseif($isPermintaan)
                                            <span class="text-muted">-</span>
                                        @elseif($isPerbaikan)
                                            {{ isset($wo->unit) && $wo->unit && $wo->unit !== '-' ? $wo->unit : '-' }}
                                        @else
                                            {{ is_object($wo->unit_code) ? $wo->unit_code->nama_unit : ($wo->unit_code ?? '-') }}
                                        @endif
                                    </td>
                                    {{-- Kolom Barang --}}
                                    <td>
                                        @if($isPembelian && count($barangItems) > 0)
                                            {{ implode(', ', $barangItems) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    {{-- Kolom Qty --}}
                                    <td>
                                        @if($isPembelian && count($qtyItems) > 0)
                                            {{ implode(', ', $qtyItems) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    {{-- Kolom Satuan --}}
                                    <td>
                                        @if($isPembelian && count($satuanItems) > 0)
                                            {{ implode(', ', $satuanItems) }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    {{-- Kolom Total Harga --}}
                                    <td>
                                        @if($isPembelian && $calculatedTotalHarga > 0)
                                            Rp {{ number_format($calculatedTotalHarga, 0, ',', '.') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
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
                                        <div class="action-buttons d-flex gap-2">
                                            <button type="button" class="btn btn-info btn-sm btn-view" 
                                                data-id="{{ $wo->id }}" data-toggle="modal" data-target="#modalViewWorkOrder" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="{{ route('admin.work-order.cetak', $wo->id) }}" 
                                               target="_blank" 
                                               class="btn btn-secondary btn-sm btn-icon" 
                                               title="Cetak">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            {{-- Tombol Edit dan Hapus dihapus - Admin hanya dapat melihat detail --}}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted">Belum ada data work order</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Modal Tambah Work Order dihapus - Admin tidak dapat membuat work order --}}
{{-- Modal Edit Work Order dihapus - Admin tidak dapat mengedit work order --}}

<!-- Modal View Work Order -->
<div class="modal fade" id="modalViewWorkOrder" tabindex="-1" role="dialog" aria-labelledby="modalViewWorkOrderLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewWorkOrderLabel">Detail Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>No. Surat Pengajuan:</strong></label>
                            <p id="view_no_work_order" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Tanggal:</strong></label>
                            <p id="view_tanggal" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Divisi Pengaju:</strong></label>
                            <p id="view_divisi_pengaju" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Ditujukan:</strong></label>
                            <p id="view_ditujukan" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Jenis Work Order:</strong></label>
                            <p id="view_jenis_wo" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="view_unit_container">
                            <label id="view_label_unit"><strong>Unit:</strong></label>
                            <p id="view_unit" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                </div>
                <div class="form-group" id="view_barang_container" style="display: none;">
                    <label><strong>Barang:</strong></label>
                    <div id="view_barang_table" class="border rounded" style="padding: 0; overflow: hidden;"></div>
                </div>
                <div class="form-group">
                    <label><strong>Uraian:</strong></label>
                    <p id="view_uraian" class="form-control-plaintext border p-2 rounded">-</p>
                </div>
                <div class="form-group">
                    <label><strong>Dokumentasi:</strong></label>
                    <div id="view_dokumentasi" class="form-control-plaintext border p-2 rounded">-</div>
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
    // Initialize DataTable
    var table;
    $(document).ready(function() {
        table = $('#workOrderTable').DataTable({
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "searching": true,
            "ordering": true,
            "info": true,
            "paging": true,
            "order": [[4, 'desc']], // Default sort by Hari/Tanggal column (index 4) descending (newest first)
            "columnDefs": [
                {
                    "targets": 0,
                    "orderable": false,
                    "searchable": false
                }
            ],
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

        // Auto-generate row numbers on every draw (always sequential 1, 2, 3...)
        table.on('order.dt search.dt draw.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = table.page.info().start + i + 1;
            });
        }).draw();
    });

    // Tutup otomatis alert setelah 3 detik
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);

    // Handle tombol View - load data via AJAX
    $('.btn-view').on('click', function() {
        var id = $(this).data('id');
        var url = '{{ url("/admin/work-order/get") }}/' + id;
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#view_no_work_order').text(data.no_work_order || data.no_surat_pengajuan || '-');
                $('#view_tanggal').text(data.tanggal ? new Date(data.tanggal).toLocaleDateString('id-ID') : '-');
                $('#view_divisi_pengaju').text(data.divisi_pengaju || '-');
                $('#view_ditujukan').text(data.ditujukan || '-');
                $('#view_jenis_wo').text(data.jenis_wo || '-');
                
                // Cek jenis work order
                const jenisWo = data.jenis_wo ? data.jenis_wo.toLowerCase() : '';
                const isPembelian = jenisWo === 'pembelian';
                
                // Parse format dengan qty dan tampilkan dalam table
                let unitDisplay = '-';
                let barangTable = '';
                
                // Helper function untuk mengambil satuan dari data barang (jika tersedia)
                function getSatuanByBarangName(barangName) {
                    // Cek apakah ada data satuan_lookup dari server
                    if (data.satuan_lookup && data.satuan_lookup[barangName]) {
                        return data.satuan_lookup[barangName];
                    }
                    return '-';
                }
                
                if (isPembelian) {
                    // Jika jenis WO adalah Pembelian, tampilkan tabel di div baru
                    if (Array.isArray(data.unit)) {
                        // Jika array, buat table dengan qty dan satuan
                        if (data.unit.length > 0) {
                            barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                            barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 37%;">Nama Barang</th><th style="width: 15%; text-align: center !important;">Qty</th><th style="width: 20%; text-align: center !important;">Satuan</th></tr></thead><tbody>';
                            data.unit.forEach(function(item, index) {
                                const qtyMatch = item.match(/\(qty:\s*(\d+)\)/);
                                if (qtyMatch) {
                                    const qty = qtyMatch[1];
                                    const barangName = item.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                    const satuan = getSatuanByBarangName(barangName);
                                    barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 37%;">${barangName}</td><td style="width: 15%; text-align: center !important;"><strong>${qty}</strong></td><td style="width: 20%; text-align: center !important;">${satuan}</td></tr>`;
                                } else {
                                    const satuan = getSatuanByBarangName(item);
                                    barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 37%;">${item}</td><td style="width: 15%; text-align: center !important;"><strong>-</strong></td><td style="width: 20%; text-align: center !important;">${satuan}</td></tr>`;
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
                                barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 37%;">Nama Barang</th><th style="width: 15%; text-align: center !important;">Qty</th><th style="width: 20%; text-align: center !important;">Satuan</th></tr></thead><tbody>';
                                parts.forEach(function(part, index) {
                                    const qtyMatch = part.match(/\(qty:\s*(\d+)\)/);
                                    if (qtyMatch) {
                                        const qty = qtyMatch[1];
                                        const barangName = part.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                        const satuan = getSatuanByBarangName(barangName);
                                        barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 37%;">${barangName}</td><td style="width: 15%; text-align: center !important;"><strong>${qty}</strong></td><td style="width: 20%; text-align: center !important;">${satuan}</td></tr>`;
                                    } else {
                                        const satuan = getSatuanByBarangName(part);
                                        barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 37%;">${part}</td><td style="width: 15%; text-align: center !important;"><strong>-</strong></td><td style="width: 20%; text-align: center !important;">${satuan}</td></tr>`;
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
                                const satuan = getSatuanByBarangName(barangName);
                                barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                                barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 37%;">Nama Barang</th><th style="width: 15%; text-align: center !important;">Qty</th><th style="width: 20%; text-align: center !important;">Satuan</th></tr></thead><tbody>';
                                barangTable += `<tr><td style="width: 8%;">1</td><td style="width: 37%;">${barangName}</td><td style="width: 15%; text-align: center !important;"><strong>${qty}</strong></td><td style="width: 20%; text-align: center !important;">${satuan}</td></tr>`;
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
                    $('#view_unit_container').hide();
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
                    $('#view_unit_container').show();
                    $('#view_barang_container').hide();
                }
                
                // Set status dengan badge berwarna sesuai status
                var statusText = data.status || '-';
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
                
                $('#view_uraian').text(data.uraian || '-');
                if (data.dokumentasi && data.dokumentasi !== '-') {
                    // Cek apakah file adalah gambar
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    
                    if (isImage) {
                        // Tampilkan button lihat foto
                        $('#view_dokumentasi').html(
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-view-dokumentasi-modal" ' +
                            'data-foto="/storage/' + data.dokumentasi + '" ' +
                            'data-nama="' + (data.no_work_order || data.no_surat_pengajuan) + '">' +
                            '<i class="fas fa-image"></i> Lihat Foto' +
                            '</button>'
                        );
                    } else {
                        // Untuk file non-gambar, tampilkan button download
                        $('#view_dokumentasi').html(
                            '<a href="/storage/' + data.dokumentasi + '" target="_blank" class="btn btn-sm btn-outline-primary">' +
                            '<i class="fas fa-file"></i> Lihat Dokumentasi' +
                            '</a>'
                        );
                    }
                } else {
                    $('#view_dokumentasi').html('<span class="text-muted">-</span>');
                }
            },
            error: function() {
                console.error('Error loading work order data');
                alert('Gagal mengambil data work order');
            }
        });
    });

    // Handle tombol Edit - dihapus karena admin tidak dapat mengedit work order
    // Handle tombol Delete - dihapus karena admin tidak dapat menghapus work order

    // View Dokumentasi (dari modal view work order - tutup modal detail dulu, lalu buka modal foto)
    $(document).on('click', '.btn-view-dokumentasi-modal', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fotoUrl = $(this).data('foto');
        const namaWo = $(this).data('nama');
        
        // Set data foto
        $('#dokumentasiViewAdmin').attr('src', fotoUrl);
        $('#dokumentasiViewAdmin').attr('alt', 'Dokumentasi ' + namaWo);
        $('#namaWoViewAdmin').text('Dokumentasi Work Order: ' + namaWo);
        
        // Tutup modal work order terlebih dahulu
        $('#modalViewWorkOrder').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#modalViewWorkOrder').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiAdmin').modal('show');
            // Hapus event listener setelah digunakan
            $('#modalViewWorkOrder').off('hidden.bs.modal');
        });
    });

</script>
@include('components.delete-confirm-modal')

<!-- Modal View Dokumentasi -->
<div class="modal fade" id="modalViewDokumentasiAdmin" tabindex="-1" role="dialog" aria-labelledby="modalViewDokumentasiAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewDokumentasiAdminLabel">Dokumentasi Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="dokumentasiViewAdmin" src="" alt="Dokumentasi" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0" id="namaWoViewAdmin"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection
