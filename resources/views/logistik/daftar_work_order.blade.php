@extends('logistik.master')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Daftar Pengajuan Work Order')

@section('styles')
<style>
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
        min-width: 200px !important;
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
    
    /* FIX empty table message to appear in first column (No) and left-aligned */
    .dataTables_empty,
    table.dataTable tbody tr td.dataTables_empty,
    table.dataTable tbody tr td:first-child.dataTables_empty,
    .dataTables_empty td,
    table.dataTable tbody tr td[colspan].dataTables_empty {
        text-align: left !important;
        padding-left: 15px !important;
        padding-right: 0 !important;
    }

    /* FIX DataTable dropdown border */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        border: 1px solid #ddd !important;
        border-radius: 4px !important;
        padding: 5px 10px !important;
    }
    
    /* View Work Order Modal Styles */
    #viewWorkOrderModal .modal-body {
        max-height: 80vh;
        overflow-y: auto !important;
        overflow-x: auto !important;
        padding: 20px 30px;
        scrollbar-width: thin;
        scrollbar-color: #1B3C88 #f1f1f1;
    }
    
    /* Override table styles inside modal to prevent cutoff */
    #viewWorkOrderModal .table,
    #view_barang_table .table {
        min-width: auto !important;
        width: 100% !important;
        table-layout: fixed !important;
    }
    
    #viewWorkOrderModal .table th:last-child,
    #viewWorkOrderModal .table td:last-child,
    #view_barang_table .table th:last-child,
    #view_barang_table .table td:last-child {
        min-width: auto !important;
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
    
    /* Modal Preview Stok Barang Styles */
    #modalPreviewStok .modal-dialog {
        max-width: 1110px !important;
        width: 95% !important;
        margin: 1.75rem auto;
    }
    
    #modalPreviewStok .modal-content {
        border: none;
        border-radius: 8px;
        box-shadow: 0 5px 30px rgba(0,0,0,0.15);
    }
    
    #modalPreviewStok .modal-header {
        background-color: #fff !important;
        border-bottom: none;
        padding: 20px 25px 10px 25px;
    }
    
    #modalPreviewStok .modal-header .modal-title {
        color: #333;
        font-size: 20px;
        font-weight: 600;
    }
    
    #modalPreviewStok .modal-header .close {
        color: #999 !important;
        opacity: 1;
    }
    
    #modalPreviewStok .modal-body {
        padding: 10px 25px 20px 25px;
        overflow-x: auto;
    }
    
    #modalPreviewStok.stok-kurang .modal-header {
        background-color: #fff !important;
    }
    
    /* Table Title Style */
    .table-title-section {
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 12px;
        margin-bottom: 10px;
    }
    
    .table-title-section h6 {
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
        font-size: 14px;
    }
    
    .table-title-section p {
        color: #888;
        margin-bottom: 0;
        font-size: 13px;
    }
    
    /* Table Container */
    #modalPreviewStok .table-responsive {
        overflow-x: auto;
        margin: 0 -25px;
        padding: 0 25px;
        width: calc(100% + 50px);
    }
    
    /* Clean Minimalist Table */
    .stok-info-table {
        width: 100%;
        margin: 0;
        font-size: 13px;
        border-collapse: collapse;
        background: #fff;
    }
    
    .stok-info-table thead th {
        background-color: transparent;
        color: #888;
        font-weight: 500;
        padding: 12px 10px;
        text-align: left;
        font-size: 12px;
        border-bottom: 1px solid #e0e0e0;
        white-space: nowrap;
    }
    
    /* Column alignment */
    .stok-info-table thead th:nth-child(1) { width: 40px; text-align: center; }
    .stok-info-table thead th:nth-child(2) { }
    .stok-info-table thead th:nth-child(3) { width: 60px; text-align: center; }
    .stok-info-table thead th:nth-child(4) { width: 60px; text-align: center; }
    .stok-info-table thead th:nth-child(5) { width: 70px; text-align: center; }
    .stok-info-table thead th:nth-child(6) { width: 80px; text-align: center; }
    
    .stok-info-table tbody td {
        padding: 14px 10px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
        color: #333;
        vertical-align: middle;
    }
    
    .stok-info-table tbody td:nth-child(1) { text-align: center; color: #888; }
    .stok-info-table tbody td:nth-child(3) { text-align: center; }
    .stok-info-table tbody td:nth-child(4) { text-align: center; }
    .stok-info-table tbody td:nth-child(5) { text-align: center; }
    .stok-info-table tbody td:nth-child(6) { text-align: center; }
    
    .stok-info-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .stok-info-table tbody tr:hover {
        background-color: #fafafa;
    }
    
    .stok-cukup {
        color: #28a745;
        font-weight: 600;
    }
    
    .stok-kurang {
        color: #e67e22;
        font-weight: 600;
    }
    
    .stok-habis {
        color: #dc3545;
        font-weight: 600;
    }
    
    .badge-stok-cukup {
        background-color: #e8f5e9;
        color: #28a745;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .badge-stok-kurang {
        background-color: #fff8e1;
        color: #e67e22;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
        white-space: nowrap;
    }
    
    .badge-stok-habis {
        background-color: #ffebee;
        color: #dc3545;
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: 500;
        white-space: nowrap;
    }
    
    /* Modal Footer */
    #modalPreviewStok .modal-footer {
        border-top: 1px solid #e0e0e0;
        padding: 15px 25px;
    }
    
    .info-summary {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 12px;
        font-size: 13px;
    }
    
    .info-summary.success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    
    .info-summary.warning {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
    }
    
    .info-summary.danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Daftar Pengajuan Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('logistik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Daftar Pengajuan Work Order</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Pengajuan Work Order</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="daftarPengajuanTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No Work-Order</th>
                                        <th>Jenis WO</th>
                                        <th>Divisi Pengaju</th>
                                        <th>Hari/Tanggal</th>
                                        <th>Unit/Code</th>
                                        <th>Barang</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Uraian</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($workOrders as $i => $wo)
                                        @php
                                            $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                                            $jenisWo = $wo->jenisWorkOrder ? strtolower($wo->jenisWorkOrder->nama_jenis_wo) : '';
                                            $isPembelian = $jenisWo === 'pembelian';
                                            $isPermintaan = $jenisWo === 'permintaan';
                                            $isPerbaikan = $jenisWo === 'perbaikan';
                                            
                                            $barangItems = [];
                                            $qtyItems = [];
                                            $satuanItems = [];
                                            
                                            $barangLookup = isset($daftarBarang) ? collect($daftarBarang)->keyBy('nama_barang') : collect();
                                            
                                            if ($isPembelian && $wo->unit && $wo->unit !== '-') {
                                                $parts = explode(', ', $wo->unit);
                                                foreach ($parts as $part) {
                                                    if (preg_match('/^(.+?)\s*\(qty:\s*(\d+)\)$/i', trim($part), $matches)) {
                                                        $namaBarang = trim($matches[1]);
                                                        $barangItems[] = $namaBarang;
                                                        $qtyItems[] = (int)$matches[2];
                                                        $satuanItems[] = $barangLookup->get($namaBarang)->satuan ?? '-';
                                                    } elseif (!empty(trim($part))) {
                                                        $namaBarang = trim($part);
                                                        $barangItems[] = $namaBarang;
                                                        $qtyItems[] = 1;
                                                        $satuanItems[] = $barangLookup->get($namaBarang)->satuan ?? '-';
                                                    }
                                                }
                                            }
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
                                            
                                            <td>
                                                @if($isPembelian)
                                                    <span class="text-muted">-</span>
                                                @elseif($isPermintaan)
                                                    <span class="text-muted">-</span>
                                                @elseif($isPerbaikan)
                                                    {{ $wo->unit && $wo->unit !== '-' ? $wo->unit : '-' }}
                                                @else
                                                    {{ $wo->unit ?? '-' }}
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($isPembelian && count($barangItems) > 0)
                                                    {{ implode(', ', $barangItems) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($isPembelian && count($qtyItems) > 0)
                                                    {{ implode(', ', $qtyItems) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($isPembelian && count($satuanItems) > 0)
                                                    {{ implode(', ', $satuanItems) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            
                                            <td>{{ Str::limit($wo->uraian, 30) }}</td>
                                            <td>
                                                @if($status == 'Disetujui' || $status == 'Selesai')
                                                    <span class="badge badge-success">{{ $status }}</span>
                                                @elseif(Str::contains($status, 'Ditolak'))
                                                    <span class="badge badge-danger">{{ $status }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ $status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <button type="button" class="btn btn-info btn-sm btn-view" 
                                                        data-id="{{ $wo->id_surat_pengajuan }}" 
                                                        data-toggle="modal" 
                                                        data-target="#viewWorkOrderModal"
                                                        title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($status == 'Menunggu')
                                                        {{-- Tombol Setujui - Cek Stok dulu sebelum approve --}}
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm btn-icon btn-cek-stok" 
                                                                data-wo-id="{{ $wo->id_surat_pengajuan }}"
                                                                data-no-wo="{{ $wo->no_surat_pengajuan }}"
                                                                data-jenis-wo="{{ $wo->jenisWorkOrder ? strtolower($wo->jenisWorkOrder->nama_jenis_wo) : '' }}"
                                                                title="Setujui">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <form action="{{ route('logistik.reject-work-order', $wo->id_surat_pengajuan) }}" method="POST" class="reject-form" style="display:inline;" 
                                                            data-message="Yakin ingin menolak work order ini?"
                                                            data-wo-id="{{ $wo->id_surat_pengajuan }}">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-danger btn-sm btn-icon reject-btn" 
                                                                    data-wo-id="{{ $wo->id_surat_pengajuan }}"
                                                                    title="Tolak">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-success btn-sm btn-icon" 
                                                                style="background-color: #6c757d !important; border-color: #6c757d !important; cursor: not-allowed;" 
                                                                disabled>
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-danger btn-sm btn-icon" 
                                                                style="background-color: #6c757d !important; border-color: #6c757d !important; cursor: not-allowed;" 
                                                                disabled>
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    @endif
                                                    {{-- Hanya Logistik yang punya button cetak --}}
                                                    @php
                                                        $userPeran = Session::get('user_peran');
                                                        $userDivisi = Session::get('user_divisi');
                                                        $logistikDivisiId = \Illuminate\Support\Facades\DB::table('divisi')->where('nama_divisi', 'Logistik')->value('id_divisi');
                                                    @endphp
                                                    @if($userPeran == 1 || ($userPeran == 2 && $userDivisi == $logistikDivisiId))
                                                        <a href="{{ route('logistik.work-order.cetak', $wo->id_surat_pengajuan) }}" target="_blank" 
                                                           class="btn btn-secondary btn-sm btn-icon" title="Cetak">
                                                            <i class="fas fa-print"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted">Belum ada data work order</td>
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
                </div>
                <div class="form-group" id="view_barang_container" style="display: none;">
                    <label><strong>Daftar Barang:</strong></label>
                    <div id="view_barang_table" class="mt-2"></div>
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

<!-- Modal Preview Stok Barang -->
<div class="modal fade" id="modalPreviewStok" tabindex="-1" role="dialog" aria-labelledby="modalPreviewStokLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPreviewStokLabel">Preview Stok Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Info Summary -->
                <div id="stokSummary" class="info-summary">
                    <i id="stokSummaryIcon" class="fas fa-info-circle mr-2"></i>
                    <span id="stokSummaryText"></span>
                </div>
                
                <!-- Table Title Section -->
                <div class="table-title-section">
                    <h6>No. Work Order</h6>
                    <p id="previewNoWo"></p>
                </div>
                
                <!-- Tabel Stok Barang -->
                <div class="table-responsive">
                    <table class="table stok-info-table" id="tabelStokPreview">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Stok</th>
                                <th>Satuan</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="stokPreviewBody">
                            <!-- Data akan diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Info Kekurangan (jika ada) -->
                <div id="infoKekurangan" class="alert alert-warning" style="display: none; margin-top: 15px;">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span id="infoKekuranganText"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Batal
                </button>
                <button type="button" class="btn btn-success" id="btnConfirmApprove" style="display: none;">
                    <i class="fas fa-check mr-1"></i> Setujui & Serahkan Barang
                </button>
                <button type="button" class="btn btn-warning" id="btnForwardPurchasing" style="display: none;">
                    <i class="fas fa-shopping-cart mr-1"></i> Teruskan ke Purchasing
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#daftarPengajuanTable').DataTable({
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
                "emptyTable": "Tidak ada data pengajuan work order"
            }
        });

        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            viewWorkOrder(id);
        });

        $(document).on('submit', '.approve-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const message = form.data('message') || 'Yakin ingin menyetujui work order ini?';
            showApproveRejectConfirm(url, 'approve', message);
        });

        $(document).on('submit', '.reject-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const message = form.data('message') || 'Yakin ingin menolak work order ini?';
            showApproveRejectConfirm(url, 'reject', message);
        });
    });

    function viewWorkOrder(id) {
        console.log('[viewWorkOrder v2] Loading work order:', id);
        fetch(`/logistik/api/work-order/${id}`)
            .then(response => response.json())
            .then(data => {
                console.log('[viewWorkOrder v2] Data received:', data);
                console.log('[viewWorkOrder v2] satuan_lookup:', data.satuan_lookup);
                
                $('#view_no_wo').text(data.no_surat_pengajuan || data.no_work_order);
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#view_divisi_pengaju').text(data.divisi_pengaju);
                $('#view_ditujukan').text(data.ditujukan);
                
                // Cek jenis work order
                const jenisWo = data.jenis_wo ? data.jenis_wo.toLowerCase() : '';
                const isPembelian = jenisWo === 'pembelian';
                
                // Helper function untuk mengambil satuan dari data satuan_lookup
                function getSatuanByBarangName(barangName) {
                    if (data.satuan_lookup && data.satuan_lookup[barangName]) {
                        return data.satuan_lookup[barangName];
                    }
                    return '-';
                }
                
                // Parse format dengan qty dan tampilkan dalam table
                let unitDisplay = '-';
                let barangTable = '';
                
                // Jika jenis WO adalah Pembelian, tampilkan tabel barang (sama dengan kadivmekanik)
                if (isPembelian) {
                    // Tampilkan tabel di div baru
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
                            const barangName = data.unit.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                            const satuan = getSatuanByBarangName(barangName);
                            const qty = qtyMatch ? qtyMatch[1] : '1';
                            
                            barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                            barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 37%;">Nama Barang</th><th style="width: 15%; text-align: center !important;">Qty</th><th style="width: 20%; text-align: center !important;">Satuan</th></tr></thead><tbody>';
                            barangTable += `<tr><td style="width: 8%;">1</td><td style="width: 37%;">${barangName}</td><td style="width: 15%; text-align: center !important;"><strong>${qty}</strong></td><td style="width: 20%; text-align: center !important;">${satuan}</td></tr>`;
                            barangTable += '</tbody></table>';
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
                    // Jika tidak ada data barang, tampilkan di field Unit seperti biasa
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
                    $('#view_unit').html(unitDisplay || '-');
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
                
                $('#view_uraian').text(data.uraian);
                if (data.dokumentasi && data.dokumentasi !== '-') {
                    // Cek apakah file adalah gambar
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    
                    if (isImage) {
                        // Tampilkan button lihat foto
                        $('#view_dokumentasi').html(
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-view-dokumentasi-modal" ' +
                            'data-foto="/storage/' + data.dokumentasi + '" ' +
                            'data-nama="' + (data.no_surat_pengajuan || data.no_work_order) + '">' +
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
                $('#viewWorkOrderModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data pengajuan work order');
            });
    }

    // View Dokumentasi (dari modal view pengajuan - tutup modal detail dulu, lalu buka modal foto)
    $(document).on('click', '.btn-view-dokumentasi-modal', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fotoUrl = $(this).data('foto');
        const namaWo = $(this).data('nama');
        
        // Set data foto
        $('#dokumentasiViewLogistik').attr('src', fotoUrl);
        $('#dokumentasiViewLogistik').attr('alt', 'Dokumentasi ' + namaWo);
        $('#namaWoViewLogistik').text('Dokumentasi Work Order: ' + namaWo);
        
        // Tutup modal work order terlebih dahulu
        $('#viewWorkOrderModal').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#viewWorkOrderModal').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiLogistik').modal('show');
            // Hapus event listener setelah digunakan
            $('#viewWorkOrderModal').off('hidden.bs.modal');
        });
    });

    // =============================
    // CEK STOK BARANG SEBELUM APPROVE
    // =============================
    var currentWoId = null;
    var currentStokInfo = [];
    var allStokCukup = false;

    // Handler untuk tombol cek stok (setujui)
    $(document).on('click', '.btn-cek-stok', function() {
        const btn = $(this);
        const woId = btn.data('wo-id');
        const noWo = btn.data('no-wo');
        const jenisWo = btn.data('jenis-wo');
        
        currentWoId = woId;
        
        // Tampilkan loading
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Cek stok barang via API
        $.ajax({
            url: `/logistik/work-order/${woId}/cek-stok`,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                
                if (response.success) {
                    if (!response.has_barang) {
                        // Work order tidak memiliki daftar barang (bukan jenis pembelian)
                        // Langsung approve tanpa cek stok
                        showSimpleApproveConfirm(woId, noWo);
                    } else {
                        // Tampilkan modal preview stok
                        showModalPreviewStok(response, noWo, woId);
                    }
                } else {
                    alert('Gagal mengecek stok: ' + (response.message || 'Error tidak diketahui'));
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check"></i>');
                console.error('Error:', xhr);
                
                // Fallback: jika API tidak tersedia, langsung approve
                showSimpleApproveConfirm(woId, noWo);
            }
        });
    });

    // Fungsi untuk menampilkan modal preview stok
    function showModalPreviewStok(response, noWo, woId) {
        currentStokInfo = response.stok_info || [];
        allStokCukup = response.all_stok_cukup;
        
        // Set No WO
        $('#previewNoWo').text(noWo);
        
        // Build tabel stok
        let tableHtml = '';
        let totalKekurangan = 0;
        
        if (currentStokInfo.length > 0) {
            currentStokInfo.forEach(function(item, index) {
                let statusClass = 'stok-cukup';
                let badgeClass = 'badge-stok-cukup';
                let statusText = 'Cukup';
                
                if (item.status_stok === 'kurang') {
                    statusClass = 'stok-kurang';
                    badgeClass = 'badge-stok-kurang';
                    statusText = 'Kurang ' + item.kekurangan;
                    totalKekurangan += item.kekurangan;
                } else if (item.status_stok === 'habis') {
                    statusClass = 'stok-habis';
                    badgeClass = 'badge-stok-habis';
                    statusText = 'Habis';
                    totalKekurangan += item.jumlah_diminta;
                }
                
                tableHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.nama_barang}</td>
                        <td>${item.jumlah_diminta}</td>
                        <td class="${statusClass}">${item.stok_tersedia}</td>
                        <td>${item.satuan || '-'}</td>
                        <td><span class="badge ${badgeClass}">${statusText}</span></td>
                    </tr>
                `;
            });
        } else {
            tableHtml = '<tr><td colspan="6" class="text-center text-muted">Tidak ada data barang</td></tr>';
        }
        
        $('#stokPreviewBody').html(tableHtml);
        
        // Set summary info
        if (allStokCukup) {
            $('#stokSummary').removeClass('warning danger').addClass('success');
            $('#stokSummaryIcon').removeClass('fa-exclamation-triangle fa-times-circle').addClass('fa-check-circle');
            $('#stokSummaryText').html('<strong>Semua stok barang mencukupi!</strong> Barang siap diserahkan ke divisi pengaju.');
            $('#modalPreviewStok').removeClass('stok-kurang');
            
            // Tampilkan tombol approve
            $('#btnConfirmApprove').show();
            $('#btnForwardPurchasing').hide();
            $('#infoKekurangan').hide();
        } else {
            if (response.ada_barang_habis) {
                $('#stokSummary').removeClass('success warning').addClass('danger');
                $('#stokSummaryIcon').removeClass('fa-check-circle fa-exclamation-triangle').addClass('fa-times-circle');
                $('#stokSummaryText').html('<strong>Ada barang yang stoknya habis!</strong> Silakan teruskan ke Purchasing untuk pembelian.');
            } else {
                $('#stokSummary').removeClass('success danger').addClass('warning');
                $('#stokSummaryIcon').removeClass('fa-check-circle fa-times-circle').addClass('fa-exclamation-triangle');
                $('#stokSummaryText').html('<strong>Stok beberapa barang tidak mencukupi!</strong> Silakan teruskan ke Purchasing untuk pembelian.');
            }
            
            $('#modalPreviewStok').addClass('stok-kurang');
            
            // Tampilkan info kekurangan
            $('#infoKekuranganText').text('Total kekurangan: ' + totalKekurangan + ' item perlu dibeli.');
            $('#infoKekurangan').show();
            
            // Tampilkan tombol forward ke purchasing
            $('#btnConfirmApprove').hide();
            $('#btnForwardPurchasing').show();
        }
        
        // Simpan woId untuk digunakan saat konfirmasi
        $('#btnConfirmApprove').data('wo-id', woId);
        $('#btnForwardPurchasing').data('wo-id', woId);
        
        // Tampilkan modal
        $('#modalPreviewStok').modal('show');
    }

    // Fungsi untuk konfirmasi approve sederhana (untuk WO non-pembelian)
    function showSimpleApproveConfirm(woId, noWo) {
        if (typeof showApproveRejectConfirm === 'function') {
            showApproveRejectConfirm(`/logistik/work-order/approve/${woId}`, 'approve', 'Yakin ingin menyetujui work order ' + noWo + '?');
        } else {
            if (confirm('Yakin ingin menyetujui work order ' + noWo + '?')) {
                prosesApproveWorkOrder(woId);
            }
        }
    }

    // Handler untuk tombol konfirmasi approve di modal preview
    $(document).on('click', '#btnConfirmApprove', function() {
        const woId = $(this).data('wo-id');
        
        // Tutup modal preview
        $('#modalPreviewStok').modal('hide');
        
        // Proses serahkan barang langsung
        prosesSerahkanBarangLangsung(woId);
    });

    // Handler untuk tombol forward ke purchasing
    $(document).on('click', '#btnForwardPurchasing', function() {
        const woId = $(this).data('wo-id');
        
        // Tutup modal preview
        $('#modalPreviewStok').modal('hide');
        
        // Proses forward ke purchasing
        prosesForwardToPurchasing(woId);
    });

    // Fungsi untuk proses serahkan barang langsung
    function prosesSerahkanBarangLangsung(woId) {
        // Tampilkan loading
        const btn = $('#btnConfirmApprove');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        
        $.ajax({
            url: `/logistik/proses-serahkan-barang-langsung/${woId}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                hasil_cek: JSON.stringify(currentStokInfo)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Redirect ke halaman serahkan barang
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.href = '/logistik/serahkan-barang?from=crud';
                    }
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Setujui & Serahkan Barang');
                    alert('Gagal memproses: ' + (response.message || 'Error tidak diketahui'));
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Setujui & Serahkan Barang');
                console.error('Error:', xhr);
                let errorMessage = 'Gagal memproses. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    }

    // Fungsi untuk proses approve work order
    function prosesApproveWorkOrder(woId) {
        $.ajax({
            url: `/logistik/work-order/approve/${woId}`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    alert('Gagal menyetujui: ' + (response.message || 'Error tidak diketahui'));
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                let errorMessage = 'Gagal menyetujui. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    }

    // Fungsi untuk forward ke purchasing
    function prosesForwardToPurchasing(woId) {
        const btn = $('#btnForwardPurchasing');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...');
        
        $.ajax({
            url: `/logistik/work-order/${woId}/forward-to-purchasing`,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                stok_info: JSON.stringify(currentStokInfo)
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        window.location.reload();
                    }
                } else {
                    btn.prop('disabled', false).html('<i class="fas fa-shopping-cart mr-1"></i> Teruskan ke Purchasing');
                    alert('Gagal meneruskan: ' + (response.message || 'Error tidak diketahui'));
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-shopping-cart mr-1"></i> Teruskan ke Purchasing');
                console.error('Error:', xhr);
                let errorMessage = 'Gagal meneruskan ke Purchasing. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    }


</script>

<!-- Modal View Dokumentasi -->
<div class="modal fade" id="modalViewDokumentasiLogistik" tabindex="-1" role="dialog" aria-labelledby="modalViewDokumentasiLogistikLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewDokumentasiLogistikLabel">Dokumentasi Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="dokumentasiViewLogistik" src="" alt="Dokumentasi" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0" id="namaWoViewLogistik"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

