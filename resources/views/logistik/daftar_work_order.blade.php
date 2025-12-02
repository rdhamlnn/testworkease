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

    .btn-icon img {
        width: 14px;
        filter: brightness(0) invert(1);
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
                                        <th>Jenis Kebutuhan</th>
                                        <th>Daftar Barang</th>
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
                                            <td>
                                                @if($wo->jenis_kebutuhan === 'barang')
                                                    <span class="badge badge-success">Barang</span>
                                                @elseif($wo->jenis_kebutuhan === 'jasa')
                                                    <span class="badge badge-info">Jasa</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($wo->jenis_kebutuhan === 'barang' && $wo->daftar_barang)
                                                    @php
                                                        $barang = json_decode($wo->daftar_barang, true);
                                                        $jumlah = is_array($barang) ? count($barang) : 0;
                                                    @endphp
                                                    <span class="badge badge-primary">{{ $jumlah }} item</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $wo->divisi_pengaju }}</td>
                                            <td>{{ $wo->ditujukan }}</td>
                                            <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                            <td>{{ $wo->unit_code ?? $wo->unit }}</td>
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
                                                        data-id="{{ $wo->id_surat_pengajuan }}" 
                                                        data-toggle="modal" 
                                                        data-target="#viewPengajuanModal"
                                                        title="Lihat Detail">
                                                        <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" alt="view">
                                                    </button>
                                                    @if($status == 'Menunggu')
                                                        @if($wo->jenis_kebutuhan === 'barang')
                                                            {{-- Button untuk WO dengan jenis kebutuhan barang --}}
                                                            <button type="button" 
                                                                    class="btn btn-warning btn-sm btn-icon btn-cek-stok" 
                                                                    data-id="{{ $wo->id_surat_pengajuan }}"
                                                                    data-toggle="modal"
                                                                    data-target="#modalKonfirmasiCekStok"
                                                                    title="Cek Stok">
                                                                <i class="fas fa-search"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-info btn-sm btn-icon btn-konfirmasi-stok" 
                                                                    data-id="{{ $wo->id_surat_pengajuan }}"
                                                                    data-toggle="modal"
                                                                    data-target="#modalKonfirmasiStok"
                                                                    style="display: none;"
                                                                    title="Konfirmasi Stok">
                                                                <i class="fas fa-check-circle"></i>
                                                            </button>
                                                            <button type="button" 
                                                                    class="btn btn-secondary btn-sm btn-icon btn-buat-permintaan" 
                                                                    data-id="{{ $wo->id_surat_pengajuan }}"
                                                                    data-toggle="modal"
                                                                    data-target="#modalKonfirmasiBuatPermintaan"
                                                                    style="display: none;"
                                                                    title="Buat Permintaan Barang">
                                                                <i class="fas fa-shopping-cart"></i>
                                                            </button>
                                                        @else
                                                            {{-- Button untuk WO dengan jenis kebutuhan jasa --}}
                                                            <form action="{{ route('logistik.approve-work-order', $wo->id_surat_pengajuan) }}" method="POST" class="approve-form" style="display:inline;" 
                                                                data-message="Yakin ingin menyetujui work order ini?"
                                                                data-wo-id="{{ $wo->id_surat_pengajuan }}">
                                                                @csrf
                                                                <button type="submit" 
                                                                        class="btn btn-success btn-sm btn-icon approve-btn" 
                                                                        data-wo-id="{{ $wo->id_surat_pengajuan }}"
                                                                        title="Setujui">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
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
                                                        @endif
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
                                            <td colspan="13" class="text-center text-muted">Belum ada data work order</td>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
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
        fetch(`/logistik/api/work-order/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#view_no_wo').text(data.no_surat_pengajuan || data.no_work_order);
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#view_divisi_pengaju').text(data.divisi_pengaju);
                $('#view_ditujukan').text(data.ditujukan);
                
                // Cek jenis kebutuhan (untuk logistik, gunakan jenis_kebutuhan)
                const jenisKebutuhan = data.jenis_kebutuhan ? data.jenis_kebutuhan.toLowerCase() : '';
                const isBarang = jenisKebutuhan === 'barang';
                
                // Parse format dengan qty dan tampilkan dalam table
                let unitDisplay = '-';
                let barangTable = '';
                
                if (isBarang && data.daftar_barang) {
                    // Jika jenis kebutuhan adalah barang, parse daftar_barang
                    try {
                        const daftarBarang = typeof data.daftar_barang === 'string' ? JSON.parse(data.daftar_barang) : data.daftar_barang;
                        if (Array.isArray(daftarBarang) && daftarBarang.length > 0) {
                            barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                            barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Status</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                            daftarBarang.forEach(function(item, index) {
                                const namaBarang = item.nama_barang || item;
                                const qty = item.qty || item.quantity || '-';
                                barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${namaBarang}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                            });
                            barangTable += '</tbody></table>';
                        }
                    } catch (e) {
                        console.error('Error parsing daftar_barang:', e);
                    }
                } else {
                    // Jika bukan barang atau tidak ada daftar_barang, cek jenis work order
                    const jenisWo = data.jenis_wo ? data.jenis_wo.toLowerCase() : '';
                    const isPembelian = jenisWo === 'pembelian';
                    
                    if (isPembelian) {
                        // Jika jenis WO adalah Pembelian, tampilkan tabel di div baru
                        if (Array.isArray(data.unit)) {
                            // Jika array, buat table dengan qty
                            if (data.unit.length > 0) {
                                barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                                barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Status</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
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
                                    barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Status</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                                    parts.forEach(function(part, index) {
                                        const qtyMatch = part.match(/\(qty:\s*(\d+)\)/);
                                        if (qtyMatch) {
                                            const qty = qtyMatch[1];
                                            const barangName = part.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                            barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${barangName}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                                        } else {
                                            barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${part}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>-</strong></td></tr>`;
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
                                    barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Status</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                                    barangTable += `<tr><td style="width: 8%;">1</td><td style="width: 42%;">${barangName}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                                    barangTable += '</tbody></table>';
                                }
                            }
                        }
                    }
                }
                
                // Tampilkan tabel di div baru dan sembunyikan field Unit
                if (barangTable) {
                    $('#view_barang_table').html(barangTable);
                    $('#view_barang_container').show();
                    $('#view_label_unit').closest('.form-group').hide();
                } else {
                    // Jika bukan Pembelian/Barang, tampilkan di field Unit seperti biasa
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

