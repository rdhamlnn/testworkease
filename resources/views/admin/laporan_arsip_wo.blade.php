@extends('admin.master')

@section('title', 'Laporan & Arsip WO')

@php
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
@endphp

@section('styles')
<style>
    .table th,
    .table td {
        text-align: left !important;
        vertical-align: middle;
    }

    /* Fix DataTable border bug for 100 entries */
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
    }

    .dataTables_length select:focus {
        border-color: #86b7fe !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
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

    .filter-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .filter-row {
        display: flex;
        align-items: end;
        gap: 15px;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        min-width: 150px;
    }

    .filter-group label {
        font-weight: 600;
        margin-bottom: 5px;
        color: #2d3748;
    }

    .filter-group input,
    .filter-group select {
        padding: 8px 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
    }

    .filter-group input:focus,
    .filter-group select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        outline: none;
    }

    .btn-success {
        background-color: #28a745 !important;
        border-color: #28a745 !important;
        color: #fff !important;
    }

    .btn-success:hover {
        background-color: #218838 !important;
        border-color: #218838 !important;
        color: #fff !important;
    }

    .btn-icon {
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        vertical-align: middle;
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
        color: #fff !important;
        opacity: 0.8 !important;
    }

    .table-responsive {
        display: block !important;
        width: 100%;
        overflow-x: visible !important;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100% !important;
        min-width: 1200px !important;
        table-layout: auto;
        border-collapse: collapse !important;
    }

    .table th:last-child,
    .table td:last-child {
        white-space: nowrap !important;
        min-width: 120px !important;
        padding: 10px 8px !important;
    }

    .card-body {
        overflow-x: auto !important;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    .card-body > .table-responsive {
        min-width: 1200px !important;
    }

    .dataTables_wrapper {
        width: 100% !important;
        min-width: 1200px !important;
        overflow-x: visible;
        display: block !important;
    }

    .dataTables_wrapper > .row:first-child,
    .dataTables_wrapper > .row:last-child {
        min-width: 1200px !important;
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
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <!-- Laporan & Arsip WO -->
        <div class="card">
            <div class="card-header">
                <h4>Daftar Pengajuan Semua Work Order</h4>
                <div class="card-header-form">
                    <div class="input-group">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Kategori:</label>
                            <select class="form-control" id="filter-kategori">
                                <option value="" selected>-- Semua --</option>
                                <option value="menunggu">Menunggu</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="laporanTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>No</th>
                                <th>No Work-Order</th>
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
                            @forelse($data as $i => $wo)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $wo->no_surat_pengajuan }}</td>
                                <td>{{ $wo->divisi_pengaju }}</td>
                                <td>{{ $wo->ditujukan }}</td>
                                <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                <td>{{ $wo->unit->kode_unit ?? '-' }}</td>
                                <td>{{ Str::limit($wo->uraian, 30) ?: '-' }}</td>
                                <td>
                                    @if($wo->id_verifikator == 1)
                                        <span class="badge badge-warning">Menunggu</span>
                                    @elseif($wo->id_verifikator == 2)
                                        <span class="badge badge-success">Disetujui</span>
                                    @elseif($wo->id_verifikator == 3)
                                        <span class="badge badge-danger">Ditolak</span>
                                    @else
                                        <span class="badge badge-info">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-info btn-sm btn-view" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" 
                                                data-toggle="modal" data-target="#viewWorkOrderModal" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-success btn-sm" title="Print" onclick="printLaporan({{ $wo->id }})">
                                            <i class="fas fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data laporan arsip WO</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal View Detail -->
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
                            <label class="font-weight-bold">No Work Order:</label>
                            <div id="view_no_work_order" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Divisi Pengaju:</label>
                            <div id="view_divisi_pengaju" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Ditujukan:</label>
                            <div id="view_ditujukan" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Tanggal:</label>
                            <div id="view_tanggal" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Unit/Code:</label>
                            <div id="view_unit" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Status:</label>
                            <div id="view_status" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Uraian:</label>
                    <div id="view_uraian" class="form-control-plaintext border p-2 rounded"></div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Dokumentasi:</label>
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
    // Tutup otomatis alert setelah 3 detik
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);


    // Filter function
    function filterLaporan() {
        var kategori = $('#filter-kategori').val();
        
        // Show loading
        $('#laporanTable tbody').html('<tr><td colspan="10" class="text-center"><i class="fas fa-spinner fa-spin"></i> Memfilter data...</td></tr>');
        
        // Make AJAX call to filter data
        $.ajax({
            url: '{{ route("admin.laporan.arsip-wo.filter") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                kategori: kategori
            },
            success: function(response) {
                if (response.success) {
                    updateTable(response.data);
                } else {
                    alert('Gagal memfilter data');
                }
            },
            error: function() {
                alert('Gagal memfilter data');
                $('#laporanTable tbody').html('<tr><td colspan="10" class="text-center">Tidak ada data laporan arsip WO</td></tr>');
            }
        });
    }


    // Update table with filtered data
    function updateTable(data) {
        // Clear existing data from DataTable
        if (table) {
            table.clear();
            
            // Add new data to DataTable
            if (data.length > 0) {
                data.forEach(function(item, index) {
                    var statusBadge = getStatusBadge(item.id_verifikator);
                    var row = [
                        (index + 1),
                        (item.no_surat_pengajuan || '-'),
                        (item.divisi_pengaju || '-'),
                        (item.ditujukan || '-'),
                        formatDate(item.tanggal),
                        (item.unit ? item.unit.kode_unit : '-'),
                        truncateText(item.uraian, 30),
                        statusBadge,
                        '<div class="d-flex gap-2">' +
                            '<button type="button" class="btn btn-info btn-sm btn-view" ' +
                            'data-id="' + item.id_surat_pengajuan + '" ' +
                            'data-toggle="modal" data-target="#viewWorkOrderModal">' +
                            '<i class="fas fa-eye"></i>' +
                            '</button>' +
                            '<button class="btn btn-success btn-sm" title="Print" onclick="printLaporan(' + item.id + ')">' +
                                '<i class="fas fa-print"></i>' +
                            '</button>' +
                        '</div>'
                    ];
                    table.row.add(row);
                });
            } else {
                var emptyRow = [
                    '', '', '', '', '', '', '', '', '', 
                    '<td colspan="10" class="text-center">Tidak ada data laporan arsip WO</td>'
                ];
                table.row.add(emptyRow);
            }
            
            // Draw the table with new data
            table.draw();
        }
    }

    // Get status badge
    function getStatusBadge(idVerifikator) {
        switch(idVerifikator) {
            case 1:
                return '<span class="badge badge-warning">Menunggu</span>';
            case 2:
                return '<span class="badge badge-success">Disetujui</span>';
            case 3:
                return '<span class="badge badge-danger">Ditolak</span>';
            default:
                return '<span class="badge badge-info">-</span>';
        }
    }

    // Format date function
    function formatDate(dateString) {
        if (!dateString) return '-';
        var date = new Date(dateString);
        var options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: '2-digit', 
            day: '2-digit' 
        };
        return date.toLocaleDateString('id-ID', options);
    }

    // Truncate text function
    function truncateText(text, maxLength) {
        if (!text) return '-';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }


    // Print function
    function printLaporan(id) {
        console.log('Print laporan ID:', id);
        
        // You can make AJAX call to print data
        $.ajax({
            url: '{{ route("admin.laporan.arsip-wo.print-by-id", ":id") }}'.replace(':id', id),
            type: 'GET',
            data: {
                id: id
            },
            success: function(response) {
                alert('Laporan berhasil dicetak');
            },
            error: function() {
                alert('Gagal mencetak laporan');
            }
        });
    }

    // Initialize DataTable if needed
    var table;
    $(document).ready(function() {
        table = $('#laporanTable').DataTable({
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "paging": true,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
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
                "emptyTable": "Tidak ada data laporan arsip work order"
            }
        });

        // Auto filter on page load if any filter is selected
        var kategori = $('#filter-kategori').val();
        
        if (kategori && kategori !== '') {
            filterLaporan();
        }

        // Auto filter when dropdown changes
        $('#filter-kategori').on('change', function() {
            filterLaporan();
        });

        // View detail modal
        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: '/admin/work-order/' + id,
                type: 'GET',
                success: function(data) {
                    $('#view_no_work_order').text(data.no_surat_pengajuan || '-');
                    $('#view_divisi_pengaju').text(data.divisi_pengaju || '-');
                    $('#view_ditujukan').text(data.ditujukan || '-');
                    $('#view_tanggal').text(formatDate(data.tanggal));
                    $('#view_unit').text(data.unit || '-');
                    $('#view_uraian').text(data.uraian || '-');
                    
                    // Set status
                    var statusText = '';
                    switch(data.id_verifikator) {
                        case 1: statusText = 'Menunggu'; break;
                        case 2: statusText = 'Disetujui'; break;
                        case 3: statusText = 'Ditolak'; break;
                        default: statusText = data.status || '-'; break;
                    }
                    // Set status dengan badge berwarna sesuai status
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
                    
                    // Set dokumentasi
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
                    
                    $('#viewWorkOrderModal').modal('show');
                },
                error: function() {
                    alert('Gagal mengambil data work order');
                }
            });
        });
    });

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
        $('#viewWorkOrderModal').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#viewWorkOrderModal').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiAdmin').modal('show');
            // Hapus event listener setelah digunakan
            $('#viewWorkOrderModal').off('hidden.bs.modal');
        });
    });
</script>

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
