@extends('atasan.master')

@section('title', 'Riwayat Approval')

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
        min-width: 1100px !important;
        table-layout: auto;
        border-collapse: collapse !important;
    }

    .table th:last-child,
    .table td:last-child {
        white-space: nowrap !important;
        min-width: 150px !important;
        padding: 10px 8px !important;
    }

    .card-body {
        overflow-x: auto !important;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    .card-body > .table-responsive {
        min-width: 1100px !important;
    }

    .dataTables_wrapper {
        width: 100% !important;
        min-width: 1100px !important;
        overflow-x: visible;
        display: block !important;
    }

    .dataTables_wrapper > .row:first-child,
    .dataTables_wrapper > .row:last-child {
        min-width: 1100px !important;
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
        <h1>Riwayat Permintaan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('atasan.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Riwayat Permintaan</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Riwayat Permintaan Persetujuan</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="riwayatApprovalTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No. Permintaan</th>
                                        <th>No. Work Order</th>
                                        <th>Tanggal Permintaan</th>
                                        <th>Daftar Barang</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Tanggal Approval</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($riwayatApproval as $i => $pb)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $pb->no_permintaan_barang }}</td>
                                            <td>{{ $pb->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
                                            <td>
                                                @php
                                                    $daftarBarang = $pb->daftarBarang ?? collect();
                                                @endphp
                                                @if($daftarBarang->count())
                                                    <ul class="mb-0">
                                                        @foreach($daftarBarang as $barang)
                                                            <li>{{ $barang->nama_barang ?? '-' }} ({{ $barang->jumlah ?? '-' }} {{ $barang->satuan ?? '-' }})</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($pb->total_estimasi_harga ?? 0, 0, ',', '') }}</td>
                                            <td>
                                                @if($pb->status == 'Disetujui Atasan')
                                                    <span class="badge badge-success">{{ $pb->status }}</span>
                                                @else
                                                    <span class="badge badge-danger">{{ $pb->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($pb->updated_at)->locale('id')->isoFormat('DD MMMM YYYY HH:mm') }}</td>
                                            <td>
                                                <button type="button" 
                                                        class="btn btn-info btn-sm btn-icon btn-view" 
                                                        data-id="{{ $pb->id_permintaan_barang }}" 
                                                        data-status="{{ $pb->status }}"
                                                        data-catatan="{{ addslashes($pb->catatan_atasan ?? '') }}"
                                                        data-toggle="modal" 
                                                        data-target="#viewApprovalModal" 
                                                        title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Belum ada riwayat approval</td>
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

<!-- Modal View Approval -->
<div class="modal fade" id="viewApprovalModal" tabindex="-1" role="dialog" aria-labelledby="viewApprovalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewApprovalModalLabel">Detail Approval</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="viewApprovalContent"></div>
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
        $('#riwayatApprovalTable').DataTable({
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "pageLength": 10,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": {
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });

        $(document).on('click', '.btn-view', function() {
            var row = $(this).closest('tr');
            var status = $(this).data('status');
            var catatan = ($(this).data('catatan') || '').toString();

            var content = '<div class="row">';
            content += '<div class="col-md-6"><div class="form-group"><label><strong>No. Permintaan:</strong></label><p class="form-control-plaintext border p-2 rounded">' + row.find('td:eq(1)').text() + '</p></div></div>';
            content += '<div class="col-md-6"><div class="form-group"><label><strong>No. Work Order:</strong></label><p class="form-control-plaintext border p-2 rounded">' + row.find('td:eq(2)').text() + '</p></div></div>';
            content += '</div><div class="row">';
            content += '<div class="col-md-6"><div class="form-group"><label><strong>Tanggal:</strong></label><p class="form-control-plaintext border p-2 rounded">' + row.find('td:eq(3)').text() + '</p></div></div>';
            content += '<div class="col-md-6"><div class="form-group"><label><strong>Total Harga:</strong></label><p class="form-control-plaintext border p-2 rounded">' + row.find('td:eq(5)').text() + '</p></div></div>';
            content += '</div><div class="row">';
            content += '<div class="col-12"><div class="form-group"><label><strong>Daftar Barang:</strong></label><div class="form-control-plaintext border p-2 rounded">' + row.find('td:eq(4)').html() + '</div></div></div>';
            content += '</div><div class="row">';
            content += '<div class="col-md-6"><div class="form-group"><label><strong>Status:</strong></label><div class="form-control-plaintext border p-2 rounded">' + row.find('td:eq(6)').html() + '</div></div></div>';
            content += '<div class="col-md-6"><div class="form-group"><label><strong>Tanggal Approval:</strong></label><p class="form-control-plaintext border p-2 rounded">' + row.find('td:eq(7)').text() + '</p></div></div>';
            content += '</div>';

            // Jika ditolak, tampilkan alasan penolakan (catatan_atasan)
            if (status === 'Ditolak Atasan' && catatan.trim() !== '') {
                // Ganti newline jadi <br> supaya rapi
                var catatanHtml = catatan.replace(/\n/g, '<br>');
                content += '<div class="row mt-2">';
                content += '<div class="col-12"><div class="form-group"><label><strong>Alasan Penolakan:</strong></label><div class="form-control-plaintext border p-2 rounded bg-light">' + catatanHtml + '</div></div></div>';
                content += '</div>';
            }
            $('#viewApprovalContent').html(content);
            $('#viewApprovalModal').modal('show');
        });
    });
</script>
@endsection

