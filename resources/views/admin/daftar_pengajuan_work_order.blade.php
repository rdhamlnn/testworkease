@extends('admin.master')

@section('title', 'Daftar Pengajuan Work Order')

@section('styles')
<style>
    .table th,
    .table td {
        text-align: left !important;
        vertical-align: middle;
    }

    .btn-icon img {
        width: 14px;
        filter: brightness(0) invert(1);
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
        min-width: 120px !important;
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
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Daftar Pengajuan Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('admin.work-order') }}">Work Order</a></div>
            <div class="breadcrumb-item active">Daftar Pengajuan Work Order</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <!-- Daftar Pengajuan Work Order -->
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
                                <th>Divisi Pengaju</th>
                                <th>Hari/Tanggal</th>
                                <th>Unit/Code</th>
                                <th>Uraian</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($submissionWorkOrders as $i => $wo)
                                @php
                                    $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $wo->no_work_order }}</td>
                                    <td>{{ $wo->divisi_pengaju }}</td>
                                    <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                    <td>{{ is_object($wo->unit_code) ? $wo->unit_code->nama_unit : $wo->unit_code }}</td>
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
                                                data-id="{{ $wo->id }}" data-toggle="modal" data-target="#modalViewWorkOrder" title="Lihat Detail">
                                                <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" alt="view">
                                            </button>
                                            <form action="{{ url('/admin/work-order/approve') }}/{{ $wo->id }}" method="POST" class="approve-form" style="display:inline;" 
                                                data-message="Apakah Anda yakin ingin menyetujui work order ini?">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-success btn-sm btn-icon {{ isset($wo->id_verifikator) && $wo->id_verifikator == 2 ? 'disabled' : '' }}"
                                                        style="{{ isset($wo->id_verifikator) && $wo->id_verifikator == 2 ? 'background-color: #6c757d !important; border-color: #6c757d !important; cursor: not-allowed;' : '' }}"
                                                        {{ isset($wo->id_verifikator) && $wo->id_verifikator == 2 ? 'disabled' : '' }} title="Setujui">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ url('/admin/work-order/reject') }}/{{ $wo->id }}" method="POST" class="reject-form" style="display:inline;" 
                                                data-message="Apakah Anda yakin ingin menolak work order ini?">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-danger btn-sm btn-icon {{ isset($wo->id_verifikator) && $wo->id_verifikator == 3 ? 'disabled' : '' }}"
                                                        style="{{ isset($wo->id_verifikator) && $wo->id_verifikator == 3 ? 'background-color: #6c757d !important; border-color: #6c757d !important; cursor: not-allowed;' : '' }}"
                                                        {{ isset($wo->id_verifikator) && $wo->id_verifikator == 3 ? 'disabled' : '' }} title="Tolak">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">Belum ada data pengajuan work order</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

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
                            <label><strong>No Work Order:</strong></label>
                            <p id="view_no_work_order" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Divisi Pengaju:</strong></label>
                            <p id="view_divisi_pengaju" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Ditujukan:</strong></label>
                            <p id="view_ditujukan" class="form-control-plaintext border p-2 rounded">-</p>
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
                            <label><strong>Unit/Code:</strong></label>
                            <p id="view_unit_code" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Status:</strong></label>
                            <p id="view_status" class="form-control-plaintext border p-2 rounded">-</p>
                        </div>
                    </div>
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
                $('#view_no_work_order').text(data.no_work_order || '-');
                $('#view_divisi_pengaju').text(data.divisi_pengaju || '-');
                $('#view_ditujukan').text(data.ditujukan || '-');
                $('#view_tanggal').text(data.tanggal || '-');
                $('#view_unit_code').text(data.unit_code || '-');
                
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
                alert('Gagal mengambil data work order');
            }
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
        $('#modalViewWorkOrder').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#modalViewWorkOrder').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiAdmin').modal('show');
            // Hapus event listener setelah digunakan
            $('#modalViewWorkOrder').off('hidden.bs.modal');
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
