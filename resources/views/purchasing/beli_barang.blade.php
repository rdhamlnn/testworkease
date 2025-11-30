@extends('purchasing.master')

@section('title', 'Beli Barang')

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
        min-width: 1200px !important;
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

    .btn-icon {
        padding: 5px 8px;
    }

    .btn-icon i {
        font-size: 14px;
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

    /* Styling untuk tabel di dalam modal */
    #viewPermintaanModal .modal-body .table-responsive {
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch;
        margin: 0 !important;
    }

    #viewPermintaanModal .modal-body table {
        width: 100% !important;
        max-width: 100% !important;
        min-width: auto !important;
        table-layout: auto !important;
        margin-bottom: 0 !important;
    }

    #viewPermintaanModal .modal-body table th,
    #viewPermintaanModal .modal-body table td {
        white-space: normal !important;
        word-wrap: break-word !important;
        padding: 8px !important;
    }

    #viewPermintaanModal .modal-body #view_daftar_barang {
        width: 100% !important;
        max-width: 100% !important;
        overflow: hidden !important;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Beli Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('purchasing.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Beli Barang</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Barang yang Perlu Dibeli</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="beliBarangTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No. Permintaan</th>
                                        <th>No. Work Order</th>
                                        <th>Tanggal Permintaan</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                        <th>Satuan</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permintaanBarang as $i => $pb)
                                        @php
                                            $daftarBarang = $pb->daftarBarang ?? collect();
                                            $barangCount = $daftarBarang->count() > 0 ? $daftarBarang->count() : 1;
                                        @endphp
                                        @if($daftarBarang->count() > 0)
                                            @foreach($daftarBarang as $idx => $barang)
                                                <tr>
                                                    @if($idx === 0)
                                                        <td rowspan="{{ $barangCount }}">{{ $i + 1 }}</td>
                                                        <td rowspan="{{ $barangCount }}">{{ $pb->no_permintaan_barang }}</td>
                                                        <td rowspan="{{ $barangCount }}">{{ $pb->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                                        <td rowspan="{{ $barangCount }}">{{ \Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
                                                    @endif
                                                    <td>{{ $barang->nama_barang ?? '-' }}</td>
                                                    <td>{{ $barang->jumlah ?? '-' }}</td>
                                                    <td>{{ $barang->satuan ?? '-' }}</td>
                                                    @if($idx === 0)
                                                        <td rowspan="{{ $barangCount }}">Rp {{ number_format($pb->total_estimasi_harga ?? 0, 0, ',', '.') }}</td>
                                                        <td rowspan="{{ $barangCount }}">
                                                            @php
                                                                $status = $pb->statusWo->nama_status ?? $pb->status ?? 'Menunggu';
                                                            @endphp
                                                            @if($status == 'Disetujui Atasan' || $status == 'Diterima Logistik' || $status == 'Diserahkan ke Divisi' || $status == 'Dibeli Purchasing' || $status == 'Dikirim Purchasing')
                                                                <span class="badge badge-success">{{ $status }}</span>
                                                            @elseif($status == 'Ditolak Atasan')
                                                                <span class="badge badge-danger">{{ $status }}</span>
                                                            @else
                                                                <span class="badge badge-warning">{{ $status }}</span>
                                                            @endif
                                                        </td>
                                                        <td rowspan="{{ $barangCount }}">
                                                            <div style="display: flex; gap: 5px;">
                                                                <button type="button" class="btn btn-info btn-sm btn-icon btn-view" 
                                                                    data-id="{{ $pb->id_permintaan_barang }}" 
                                                                    data-toggle="modal" 
                                                                    data-target="#viewPermintaanModal"
                                                                    title="Lihat Detail">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <form action="{{ route('purchasing.beli-barang.proses', $pb->id_permintaan_barang) }}" method="POST" class="confirm-form" style="display:inline;" 
                                                                    data-message="Yakin barang sudah dibeli?"
                                                                    data-description="Tindakan ini akan mengkonfirmasi bahwa barang sudah dibeli."
                                                                    data-button-text="Ya, Konfirmasi"
                                                                    data-button-class="btn-success">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-success btn-sm btn-icon" title="Konfirmasi Pembelian">
                                                                        <i class="fas fa-shopping-cart"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $pb->no_permintaan_barang }}</td>
                                                <td>{{ $pb->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
                                                <td class="text-muted">-</td>
                                                <td class="text-muted">-</td>
                                                <td class="text-muted">-</td>
                                                <td>Rp {{ number_format($pb->total_estimasi_harga ?? 0, 0, ',', '.') }}</td>
                                                <td>
                                                    @php
                                                        $status = $pb->statusWo->nama_status ?? $pb->status ?? 'Menunggu';
                                                    @endphp
                                                    @if($status == 'Disetujui Atasan' || $status == 'Diterima Logistik' || $status == 'Diserahkan ke Divisi' || $status == 'Dibeli Purchasing' || $status == 'Dikirim Purchasing')
                                                        <span class="badge badge-success">{{ $status }}</span>
                                                    @elseif($status == 'Ditolak Atasan')
                                                        <span class="badge badge-danger">{{ $status }}</span>
                                                    @else
                                                        <span class="badge badge-warning">{{ $status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 5px;">
                                                        <button type="button" class="btn btn-info btn-sm btn-icon btn-view" 
                                                            data-id="{{ $pb->id_permintaan_barang }}" 
                                                            data-toggle="modal" 
                                                            data-target="#viewPermintaanModal"
                                                            title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <form action="{{ route('purchasing.beli-barang.proses', $pb->id_permintaan_barang) }}" method="POST" class="confirm-form" style="display:inline;" 
                                                            data-message="Yakin barang sudah dibeli?"
                                                            data-description="Tindakan ini akan mengkonfirmasi bahwa barang sudah dibeli."
                                                            data-button-text="Ya, Konfirmasi"
                                                            data-button-class="btn-success">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm btn-icon" title="Konfirmasi Pembelian">
                                                                <i class="fas fa-shopping-cart"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">Belum ada barang yang perlu dibeli</td>
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

<!-- Modal View Permintaan -->
<div class="modal fade" id="viewPermintaanModal" tabindex="-1" role="dialog" aria-labelledby="viewPermintaanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPermintaanModalLabel">Detail Permintaan Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>No. Permintaan:</strong></label>
                            <p id="view_no_permintaan" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>No. Work Order:</strong></label>
                            <p id="view_no_wo" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Tanggal Permintaan:</strong></label>
                            <p id="view_tanggal" class="form-control-plaintext border p-2 rounded"></p>
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
                    <label><strong>Total Harga:</strong></label>
                    <p id="view_total_harga" class="form-control-plaintext border p-2 rounded"></p>
                </div>
                <div class="form-group">
                    <label><strong>Daftar Barang:</strong></label>
                    <div id="view_daftar_barang" class="mt-2"></div>
                </div>
                <div class="form-group">
                    <label><strong>Catatan Logistik:</strong></label>
                    <p id="view_catatan_logistik" class="form-control-plaintext border p-2 rounded"></p>
                </div>
                <div class="form-group" id="view_catatan_atasan_group" style="display: none;">
                    <label><strong>Catatan Atasan:</strong></label>
                    <p id="view_catatan_atasan" class="form-control-plaintext border p-2 rounded"></p>
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
        $('#beliBarangTable').DataTable({
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
                "emptyTable": "Tidak ada barang yang perlu dibeli"
            }
        });

        setTimeout(() => {
            $('.alert').fadeOut();
        }, 3000);

        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            viewPermintaan(id);
        });
    });

    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.toString().replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
    }

    function viewPermintaan(id) {
        fetch(`/purchasing/api/permintaan-barang/${id}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                $('#view_no_permintaan').text(data.no_permintaan_barang || '-');
                $('#view_no_wo').text(data.no_work_order || '-');
                
                if (data.tanggal_permintaan) {
                    var tanggal = new Date(data.tanggal_permintaan);
                    var options = { year: 'numeric', month: 'long', day: 'numeric' };
                    $('#view_tanggal').text(tanggal.toLocaleDateString('id-ID', options));
                } else {
                    $('#view_tanggal').text('-');
                }
                
                // Format total harga
                var totalHarga = data.total_estimasi_harga || 0;
                $('#view_total_harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga));
                
                // Set status dengan badge berwarna sesuai status
                var statusText = data.status || 'Menunggu';
                var badgeClass = 'badge-secondary';
                if (statusText === 'Disetujui Atasan' || statusText === 'Diterima Logistik' || statusText === 'Diserahkan ke Divisi' || statusText === 'Dibeli Purchasing' || statusText === 'Dikirim Purchasing') {
                    badgeClass = 'badge-success';
                } else if (statusText === 'Ditolak Atasan') {
                    badgeClass = 'badge-danger';
                } else {
                    badgeClass = 'badge-warning';
                }
                $('#view_status').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');
                
                // Tampilkan daftar barang
                var daftarBarangHtml = '';
                if (data.daftar_barang && data.daftar_barang.length > 0) {
                    daftarBarangHtml = '<div class="table-responsive" style="max-width: 100%; overflow-x: auto;">';
                    daftarBarangHtml += '<table class="table table-sm table-bordered table-striped mb-0" style="width: 100%; max-width: 100%; table-layout: auto;">';
                    daftarBarangHtml += '<thead class="thead-light"><tr>';
                    daftarBarangHtml += '<th style="padding: 8px; width: 40%;">Nama Barang</th>';
                    daftarBarangHtml += '<th style="padding: 8px; text-align: center; width: 15%;">Jumlah</th>';
                    daftarBarangHtml += '<th style="padding: 8px; text-align: center; width: 15%;">Satuan</th>';
                    daftarBarangHtml += '<th style="padding: 8px; text-align: right; width: 30%;">Estimasi Harga</th>';
                    daftarBarangHtml += '</tr></thead>';
                    daftarBarangHtml += '<tbody>';
                    data.daftar_barang.forEach(function(barang) {
                        var estimasiHarga = barang.estimasi_harga || 0;
                        daftarBarangHtml += '<tr>';
                        daftarBarangHtml += '<td style="padding: 8px; word-wrap: break-word;">' + escapeHtml(barang.nama_barang || '-') + '</td>';
                        daftarBarangHtml += '<td style="padding: 8px; text-align: center;">' + escapeHtml(barang.jumlah || '-') + '</td>';
                        daftarBarangHtml += '<td style="padding: 8px; text-align: center;">' + escapeHtml(barang.satuan || '-') + '</td>';
                        daftarBarangHtml += '<td style="padding: 8px; text-align: right;">Rp ' + new Intl.NumberFormat('id-ID').format(estimasiHarga) + '</td>';
                        daftarBarangHtml += '</tr>';
                    });
                    daftarBarangHtml += '</tbody></table></div>';
                } else {
                    daftarBarangHtml = '<p class="text-muted mb-0">Tidak ada data barang</p>';
                }
                $('#view_daftar_barang').html(daftarBarangHtml);
                
                // Catatan
                $('#view_catatan_logistik').text(data.catatan_logistik || '-');
                
                // Tampilkan catatan atasan jika status Ditolak Atasan
                if (statusText === 'Ditolak Atasan' && data.catatan_atasan) {
                    $('#view_catatan_atasan').text(data.catatan_atasan);
                    $('#view_catatan_atasan_group').show();
                } else {
                    $('#view_catatan_atasan_group').hide();
                }
                
                $('#viewPermintaanModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data permintaan barang');
            });
    }

    // Handler untuk confirm-form
    $(document).on('submit', '.confirm-form', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const message = form.data('message') || 'Yakin ingin melanjutkan?';
        const description = form.data('description') || 'Tindakan ini akan memproses data.';
        const buttonText = form.data('button-text') || 'Konfirmasi';
        const buttonClass = form.data('button-class') || 'btn-primary';
        const iconClass = form.data('icon-class') || 'fas fa-question-circle';
        
        // Gunakan modal konfirmasi
        if (typeof showConfirmModal === 'function') {
            showConfirmModal(url, message, description, buttonText, buttonClass, iconClass);
        } else {
            // Fallback ke confirm biasa jika modal belum tersedia
            if (confirm(message)) {
                form.off('submit').submit();
            }
        }
    });
</script>
@include('components.confirm-modal')
@endsection

