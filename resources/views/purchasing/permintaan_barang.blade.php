@extends('purchasing.master')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Daftar Permintaan Barang')

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
        <h1>Daftar Permintaan Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('purchasing.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Daftar Permintaan Barang</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Permintaan Barang</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="PermintaanBarangTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No Permintaan</th>
                                        <th>No Work-Order</th>
                                        <th>Tanggal Permintaan</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permintaanBarang as $i => $pb)
                                        @php
                                            $status = $pb->statusWo->nama_status ?? $pb->status ?? 'Menunggu';
                                            $daftarBarang = $pb->daftarBarang ?? collect();
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $pb->no_permintaan_barang }}</td>
                                            <td>{{ $pb->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                            <td>
                                                @if($pb->tanggal_permintaan)
                                                    {{ \Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($pb->total_estimasi_harga ?? 0, 0, ',', '.') }}</td>
                                            <td>
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
                                                    @php
                                                        $statusWo = $pb->statusWo ?? null;
                                                        $statusNama = $statusWo ? $statusWo->nama_status : ($pb->status ?? 'Menunggu');
                                                        $canUpdateHarga = $statusNama == 'Menunggu Purchasing';
                                                    @endphp
                                                    @if($canUpdateHarga)
                                                        <a href="{{ route('purchasing.permintaan-barang.edit-harga', $pb->id_permintaan_barang) }}" 
                                                           class="btn btn-warning btn-sm btn-icon" 
                                                           title="Update Harga">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-success btn-sm btn-icon btn-kirim-approval" 
                                                                data-id="{{ $pb->id_permintaan_barang }}"
                                                                title="Kirim ke Atasan">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Belum ada data permintaan barang</td>
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
                @if($pb->status == 'Ditolak Atasan')
                    <div class="form-group">
                        <label><strong>Catatan Atasan:</strong></label>
                        <p id="view_catatan_atasan" class="form-control-plaintext border p-2 rounded"></p>
                    </div>
                @endif
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
        $('#PermintaanBarangTable').DataTable({
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
                "emptyTable": "Tidak ada data permintaan barang"
            }
        });

        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            viewPermintaan(id);
        });

        // Handler untuk tombol kirim ke atasan
        $(document).on('click', '.btn-kirim-approval', function() {
            var id = $(this).data('id');
            
            Swal.fire({
                title: 'Kirim ke Atasan?',
                text: 'Permintaan akan dikirim ke atasan untuk approval',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kirim!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/purchasing/permintaan-barang/' + id + '/kirim-approval',
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = response.redirect;
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            var message = 'Gagal mengirim permintaan';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: message
                            });
                        }
                    });
                }
            });
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
                             $('#view_catatan_atasan').text(data.catatan_atasan || '-');
                
                $('#viewPermintaanModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data permintaan barang');
            });
    }
</script>
@endsection

