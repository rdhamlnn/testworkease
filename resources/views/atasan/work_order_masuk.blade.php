@extends('atasan.master')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Work Order Masuk')

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
    
    /* FIX empty table message */
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
        overflow-x: hidden !important;
        padding: 20px 30px;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Work Order Masuk</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('atasan.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Work Order Masuk</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Work Order yang Perlu Persetujuan</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="workOrderTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No Work-Order</th>
                                        <th>Jenis WO</th>
                                        <th>Divisi Pengaju</th>
                                        <th>Hari/Tanggal</th>
                                        <th>Unit/Code</th>
                                        <th>Uraian</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($workOrders as $i => $wo)
                                        @php
                                            // Prioritas: cek kolom status terlebih dahulu jika mengandung "Ditolak Atasan"
                                            // karena saat ditolak oleh Atasan, id_verifikator direset ke 1 (Menunggu)
                                            // tapi kolom status berisi "Ditolak Atasan - Perlu Dikirim Ulang"
                                            if ($wo->status && strpos($wo->status, 'Ditolak Atasan') !== false) {
                                                $status = $wo->status;
                                            } else {
                                                $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
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
                                            <td>{{ $wo->unit_code ?? $wo->unit }}</td>
                                            <td>{{ Str::limit($wo->uraian, 30) }}</td>
                                            <td>Rp {{ number_format($wo->calculated_total_harga ?? 0, 0, ',', '.') }}</td>
                                            <td>
                                                @if($status == 'Disetujui' || $status == 'Selesai')
                                                    <span class="badge badge-success">{{ $status }}</span>
                                                @elseif(strpos($status, 'Ditolak Atasan') !== false)
                                                    <span class="badge badge-danger">Ditolak Atasan</span>
                                                @elseif($status == 'Ditolak')
                                                    <span class="badge badge-danger">{{ $status }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ $status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <button type="button" class="btn btn-info btn-sm btn-view" 
                                                        data-id="{{ $wo->id_surat_pengajuan }}" 
                                                        data-toggle="modal" 
                                                        data-target="#viewWorkOrderModal"
                                                        title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="{{ route('atasan.work-order.cetak', $wo->id_surat_pengajuan) }}" target="_blank" 
                                                       class="btn btn-secondary btn-sm" title="Cetak">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    @if($status == 'Menunggu')
                                                        <form action="{{ route('atasan.approve-work-order', $wo->id_surat_pengajuan) }}" method="POST" class="approve-form" style="display:inline;" 
                                                            data-message="Yakin ingin menyetujui work order ini?">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-success btn-sm btn-icon" 
                                                                    title="Setujui">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        <button type="button" 
                                                                class="btn btn-danger btn-sm btn-icon btn-reject" 
                                                                data-id="{{ $wo->id_surat_pengajuan }}"
                                                                title="Tolak">
                                                            <i class="fas fa-times"></i>
                                                        </button>
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
                            <label><strong>No. Work Order:</strong></label>
                            <p id="view_no_wo" class="form-control-plaintext border p-2 rounded"></p>
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
                </div>
                <div class="row">
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
                            <label><strong>Unit:</strong></label>
                            <p id="view_unit" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Uraian:</strong></label>
                    <p id="view_uraian" class="form-control-plaintext border p-2 rounded"></p>
                </div>
                <div class="form-group" id="view_harga_barang_container" style="display: none;">
                    <label><strong>Detail Harga Barang:</strong></label>
                    <div id="view_harga_barang" class="form-control-plaintext border p-2 rounded"></div>
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

<!-- Modal Reject Work Order -->
<div class="modal fade" id="rejectWorkOrderModal" tabindex="-1" role="dialog" aria-labelledby="rejectWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectWorkOrderModalLabel">Tolak Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="rejectWorkOrderForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatan_penolakan">Alasan Penolakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="catatan_penolakan" name="catatan_penolakan" rows="4" required placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Work Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
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

        $(document).on('click', '.btn-reject', function() {
            var id = $(this).data('id');
            $('#rejectWorkOrderForm').attr('action', '{{ url("atasan/work-order/reject") }}/' + id);
            $('#catatan_penolakan').val('');
            $('#rejectWorkOrderModal').modal('show');
        });

        $(document).on('submit', '#rejectWorkOrderForm', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#rejectWorkOrderModal').modal('hide');
                        window.location.href = response.redirect || '{{ route("atasan.work-order-masuk") }}';
                    } else {
                        alert(response.message || 'Gagal menolak work order');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    alert(response.message || 'Gagal menolak work order');
                }
            });
        });
    });

    function viewWorkOrder(id) {
        fetch(`{{ url('atasan/api/work-order') }}/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#view_no_wo').text(data.no_surat_pengajuan || data.no_work_order || '-');
                $('#view_no_wo_parent').text(data.no_wo_parent || '-');
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }));
                $('#view_divisi_pengaju').text(data.divisi_pengaju || '-');
                $('#view_unit').text(data.unit_code || data.unit || '-');
                $('#view_total_harga').text('Rp ' + (data.total_harga ? new Intl.NumberFormat('id-ID').format(data.total_harga) : '0'));
                $('#view_uraian').text(data.uraian || '-');
                
                // Tampilkan detail harga barang jika ada
                if (data.harga_barang && Array.isArray(data.harga_barang) && data.harga_barang.length > 0) {
                    let hargaHtml = '<table class="table table-sm table-bordered"><thead><tr><th>Barang</th><th>Harga</th></tr></thead><tbody>';
                    data.harga_barang.forEach(function(item) {
                        hargaHtml += '<tr><td>' + (item.nama_barang || '-') + '</td><td>Rp ' + new Intl.NumberFormat('id-ID').format(item.harga || 0) + '</td></tr>';
                    });
                    hargaHtml += '</tbody></table>';
                    $('#view_harga_barang').html(hargaHtml);
                    $('#view_harga_barang_container').show();
                } else {
                    $('#view_harga_barang_container').hide();
                }
                
                // Dokumentasi - tampilkan button lihat foto
                if (data.dokumentasi && data.dokumentasi !== '-' && data.dokumentasi.trim() !== '') {
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    const dokumentasiUrl = data.dokumentasi_url || '/storage/' + data.dokumentasi;
                    
                    if (isImage) {
                        $('#view_dokumentasi').html(
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-view-dokumentasi-modal" ' +
                            'data-foto="' + dokumentasiUrl + '" ' +
                            'data-nama="' + (data.no_surat_pengajuan || data.no_work_order || '') + '">' +
                            '<i class="fas fa-image"></i> Lihat Foto' +
                            '</button>'
                        );
                    } else {
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

    // View Dokumentasi Modal Handler
    $(document).on('click', '.btn-view-dokumentasi-modal', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fotoUrl = $(this).data('foto');
        const namaWo = $(this).data('nama');
        
        // Set data foto
        $('#dokumentasiViewAtasan').attr('src', fotoUrl);
        $('#dokumentasiViewAtasan').attr('alt', 'Dokumentasi ' + namaWo);
        $('#namaWoViewAtasan').text('Dokumentasi Work Order: ' + namaWo);
        
        // Tutup modal work order terlebih dahulu
        $('#viewWorkOrderModal').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#viewWorkOrderModal').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiAtasan').modal('show');
            // Hapus event listener setelah digunakan
            $('#viewWorkOrderModal').off('hidden.bs.modal');
        });
    });
</script>

<!-- Modal View Dokumentasi -->
<div class="modal fade" id="modalViewDokumentasiAtasan" tabindex="-1" role="dialog" aria-labelledby="modalViewDokumentasiAtasanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewDokumentasiAtasanLabel">Dokumentasi Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="dokumentasiViewAtasan" src="" alt="Dokumentasi" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0" id="namaWoViewAtasan"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

