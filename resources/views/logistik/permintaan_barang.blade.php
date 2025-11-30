@extends('logistik.master')

@section('title', 'Permintaan Barang')

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
        min-width: 150px !important;
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

    /* Row untuk controls (length, filter) - Pastikan ikut scroll */
    .dataTables_wrapper > .row:first-child,
    .dataTables_wrapper .row:first-of-type {
        margin: 0 !important;
        width: 100% !important;
        min-width: 1200px !important;
        max-width: none !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
    }

    /* Row untuk info dan pagination - Pastikan ikut scroll */
    .dataTables_wrapper > .row:last-child,
    .dataTables_wrapper .row:last-of-type {
        margin: 0 !important;
        width: 100% !important;
        min-width: 1200px !important;
        max-width: none !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: space-between !important;
    }

    /* Semua row di dalam dataTables_wrapper */
    .dataTables_wrapper .row {
        margin: 0 !important;
        width: 100% !important;
        min-width: 1200px !important;
        max-width: none !important;
        display: flex !important;
        flex-wrap: nowrap !important;
    }

    .dataTables_wrapper .row > div:last-child {
        display: block !important;
        text-align: right !important;
    }

    .dataTables_wrapper .row > div:last-child .dataTables_filter {
        float: right !important;
        display: inline-block !important;
        text-align: right !important;
        margin-left: auto !important;
    }

    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        white-space: nowrap !important;
    }

    /* Fix info section agar angka terlihat */
    .dataTables_wrapper .dataTables_info {
        padding-top: 0.85em;
        white-space: nowrap !important;
        min-width: 280px !important;
        display: inline-block !important;
        width: auto !important;
    }

    /* Fix pagination agar ikut scroll dan terlihat */
    .dataTables_wrapper .dataTables_paginate {
        margin: 0 !important;
        white-space: nowrap !important;
        text-align: right !important;
        float: right !important;
        min-width: 250px !important;
        width: auto !important;
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .dataTables_wrapper .dataTables_paginate .pagination {
        margin: 0 !important;
        white-space: nowrap !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Pastikan row yang berisi pagination terlihat */
    .dataTables_wrapper .row:last-child {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .dataTables_wrapper .row:last-child > div:last-child {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
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
        <h1>Permintaan Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('logistik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Permintaan Barang</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Permintaan Barang</h4>
                        <div class="card-header-action">
                            <a href="{{ route('logistik.daftar-work-order') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Permintaan Barang
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="permintaanBarangTable">
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
                                            $daftarBarangJson = htmlspecialchars(json_encode($daftarBarang->map(function($b) { 
                                                return [
                                                    'nama' => $b->nama_barang ?? '-', 
                                                    'jumlah' => $b->jumlah ?? '-', 
                                                    'satuan' => $b->satuan ?? '-'
                                                ]; 
                                            })->toArray()), ENT_QUOTES, 'UTF-8');
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
                                                            @if($pb->status == 'Disetujui Atasan' || $pb->status == 'Diterima Logistik' || $pb->status == 'Diserahkan ke Divisi' || $pb->status == 'Dibeli Purchasing')
                                                                <span class="badge badge-success">{{ $pb->status }}</span>
                                                            @elseif($pb->status == 'Ditolak Atasan')
                                                                <span class="badge badge-danger">{{ $pb->status }}</span>
                                                            @elseif($pb->status == 'Menunggu Approval Atasan' || $pb->status == 'Menunggu Pembelian' || $pb->status == 'Menunggu Pengiriman' || $pb->status == 'Dikirim Purchasing')
                                                                <span class="badge badge-warning">{{ $pb->status }}</span>
                                                            @else
                                                                <span class="badge badge-info">{{ $pb->status }}</span>
                                                            @endif
                                                        </td>
                                                        <td rowspan="{{ $barangCount }}">
                                                            <div style="display: flex; gap: 5px;">
                                                                <button type="button" class="btn btn-info btn-sm btn-icon btn-view" 
                                                                    data-id="{{ $pb->id_permintaan_barang }}"
                                                                    data-no-permintaan="{{ htmlspecialchars($pb->no_permintaan_barang, ENT_QUOTES, 'UTF-8') }}"
                                                                    data-no-wo="{{ htmlspecialchars($pb->suratPengajuan->no_surat_pengajuan ?? '-', ENT_QUOTES, 'UTF-8') }}"
                                                                    data-tanggal="{{ htmlspecialchars(\Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY'), ENT_QUOTES, 'UTF-8') }}"
                                                                    data-total-harga="{{ number_format($pb->total_estimasi_harga ?? 0, 0, ',', '.') }}"
                                                                    data-status="{{ htmlspecialchars($pb->status, ENT_QUOTES, 'UTF-8') }}"
                                                                    data-catatan="{{ htmlspecialchars($pb->catatan_atasan ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                                    data-daftar-barang="{{ $daftarBarangJson }}"
                                                                    title="Lihat Detail">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                @php
                                                                    $statusWo = $pb->statusWo ?? null;
                                                                    $statusNama = $statusWo ? $statusWo->nama_status : $pb->status;
                                                                    $canEdit = in_array($statusNama, ['Menunggu Logistik', 'Menunggu Purchasing']);
                                                                @endphp
                                                                @if($canEdit)
                                                                    <a href="{{ route('logistik.permintaan-barang.edit', $pb->id_permintaan_barang) }}" 
                                                                       class="btn btn-warning btn-sm btn-icon" 
                                                                       title="Edit">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                    <button type="button" 
                                                                            class="btn btn-danger btn-sm btn-icon btn-delete" 
                                                                            data-id="{{ $pb->id_permintaan_barang }}"
                                                                            data-no-permintaan="{{ htmlspecialchars($pb->no_permintaan_barang, ENT_QUOTES, 'UTF-8') }}"
                                                                            title="Hapus">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                @endif
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
                                                    @if($pb->status == 'Disetujui Atasan' || $pb->status == 'Diterima Logistik' || $pb->status == 'Diserahkan ke Divisi' || $pb->status == 'Dibeli Purchasing')
                                                        <span class="badge badge-success">{{ $pb->status }}</span>
                                                    @elseif($pb->status == 'Ditolak Atasan')
                                                        <span class="badge badge-danger">{{ $pb->status }}</span>
                                                    @elseif($pb->status == 'Menunggu Approval Atasan' || $pb->status == 'Menunggu Pembelian' || $pb->status == 'Menunggu Pengiriman' || $pb->status == 'Dikirim Purchasing')
                                                        <span class="badge badge-warning">{{ $pb->status }}</span>
                                                    @else
                                                        <span class="badge badge-info">{{ $pb->status }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div style="display: flex; gap: 5px;">
                                                        <button type="button" class="btn btn-info btn-sm btn-icon btn-view" 
                                                            data-id="{{ $pb->id_permintaan_barang }}"
                                                            data-no-permintaan="{{ htmlspecialchars($pb->no_permintaan_barang, ENT_QUOTES, 'UTF-8') }}"
                                                            data-no-wo="{{ htmlspecialchars($pb->suratPengajuan->no_surat_pengajuan ?? '-', ENT_QUOTES, 'UTF-8') }}"
                                                            data-tanggal="{{ htmlspecialchars(\Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY'), ENT_QUOTES, 'UTF-8') }}"
                                                            data-total-harga="{{ number_format($pb->total_estimasi_harga ?? 0, 0, ',', '.') }}"
                                                            data-status="{{ htmlspecialchars($pb->status, ENT_QUOTES, 'UTF-8') }}"
                                                            data-catatan="{{ htmlspecialchars($pb->catatan_atasan ?? '', ENT_QUOTES, 'UTF-8') }}"
                                                            data-daftar-barang="[]"
                                                            title="Lihat Detail">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        @php
                                                            $statusWo = $pb->statusWo ?? null;
                                                            $statusNama = $statusWo ? $statusWo->nama_status : $pb->status;
                                                            $canEdit = in_array($statusNama, ['Menunggu Logistik', 'Menunggu Purchasing']);
                                                        @endphp
                                                        @if($canEdit)
                                                            <a href="{{ route('logistik.permintaan-barang.edit', $pb->id_permintaan_barang) }}" 
                                                               class="btn btn-warning btn-sm btn-icon" 
                                                               title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-danger btn-sm btn-icon btn-delete" 
                                                                    data-id="{{ $pb->id_permintaan_barang }}"
                                                                    data-no-permintaan="{{ htmlspecialchars($pb->no_permintaan_barang, ENT_QUOTES, 'UTF-8') }}"
                                                                    title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">Belum ada data permintaan barang</td>
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
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>No. Permintaan</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewNoPermintaan"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>No. Work Order</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewNoWo"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Tanggal Permintaan</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewTanggal"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Total Harga</strong></label>
                            <p class="form-control-plaintext border p-2 rounded" id="viewTotalHarga"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Daftar Barang:</strong></label>
                    <div id="view_daftar_barang" class="mt-2"></div>
                </div>
                <div class="form-group">
                    <label><strong>Status</strong></label>
                    <p class="form-control-plaintext border p-2 rounded" id="viewStatus"></p>
                </div>
                <div class="form-group" id="viewCatatanGroup" style="display: none;">
                    <label><strong>Alasan Penolakan</strong></label>
                    <div class="form-control-plaintext border p-2 rounded bg-danger text-white" id="viewCatatan"></div>
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
    let table;
    $(document).ready(function() {
        // Initialize DataTable - nonaktifkan ordering untuk menghindari konflik dengan rowspan
        table = $('#permintaanBarangTable').DataTable({
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "searching": true,
            "ordering": false, // Nonaktifkan ordering karena tidak kompatibel dengan rowspan
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

        // Auto hide alerts
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 3000);
    });

    // Handler untuk tombol view detail - letakkan di luar $(document).ready() seperti di daftar_barang
    $(document).on('click', '.btn-view', function() {
        var $btn = $(this);
            var noPermintaan = $btn.data('no-permintaan') || '-';
            var noWo = $btn.data('no-wo') || '-';
            var tanggal = $btn.data('tanggal') || '-';
            var totalHargaRaw = $btn.data('total-harga') || '0';
            var totalHarga = 'Rp ' + totalHargaRaw;
            var status = $btn.attr('data-status') || '-';
            var catatan = ($btn.attr('data-catatan') || '').toString();
            
            // Parse data daftar barang
            var daftarBarangData = [];
            try {
                var daftarBarangAttr = $btn.attr('data-daftar-barang');
                if (daftarBarangAttr) {
                    // Decode HTML entities jika ada
                    daftarBarangAttr = $('<div>').html(daftarBarangAttr).text();
                    daftarBarangData = JSON.parse(daftarBarangAttr);
                }
            } catch(e) {
                console.error('Error parsing daftar barang:', e);
                daftarBarangData = [];
            }

            // Validasi dan set data
            $('#viewNoPermintaan').text(noPermintaan || 'Tidak tersedia');
            $('#viewNoWo').text(noWo || 'Tidak tersedia');
            $('#viewTanggal').text(tanggal || 'Tidak tersedia');
            $('#viewTotalHarga').text(totalHarga || 'Rp 0');
            
            // Set status HTML - buat badge secara dinamis berdasarkan status dengan warna yang sesuai
            var badgeClass = 'badge-secondary';
            var statusText = status || 'Tidak tersedia';
            
            if (statusText === 'Disetujui Atasan' || statusText === 'Diterima Logistik' || statusText === 'Diserahkan ke Divisi' || statusText === 'Dibeli Purchasing') {
                badgeClass = 'badge-success'; // Hijau untuk status sukses
            } else if (statusText === 'Ditolak Atasan') {
                badgeClass = 'badge-danger'; // Merah untuk status ditolak
            } else if (statusText === 'Menunggu Approval Atasan' || statusText === 'Menunggu Pembelian' || statusText === 'Menunggu Pengiriman' || statusText === 'Dikirim Purchasing') {
                badgeClass = 'badge-warning'; // Kuning untuk status menunggu/pengiriman
            } else {
                badgeClass = 'badge-info'; // Biru untuk status lainnya
            }
            $('#viewStatus').html('<span class="badge ' + badgeClass + '">' + statusText + '</span>');
            
            // Build tabel daftar barang
            var daftarBarangHtml = '';
            if (Array.isArray(daftarBarangData) && daftarBarangData.length > 0) {
                daftarBarangHtml = '<div class="table-responsive" style="max-width: 100%; overflow-x: auto;">';
                daftarBarangHtml += '<table class="table table-sm table-bordered table-striped mb-0" style="width: 100%; max-width: 100%; table-layout: auto;">';
                daftarBarangHtml += '<thead class="thead-light"><tr>';
                daftarBarangHtml += '<th style="padding: 8px; width: 50%;">Nama Barang</th>';
                daftarBarangHtml += '<th style="padding: 8px; text-align: center; width: 25%;">Jumlah</th>';
                daftarBarangHtml += '<th style="padding: 8px; text-align: center; width: 25%;">Satuan</th>';
                daftarBarangHtml += '</tr></thead>';
                daftarBarangHtml += '<tbody>';
                daftarBarangData.forEach(function(barang) {
                    if (barang.nama && barang.nama !== '-') {
                        daftarBarangHtml += '<tr>';
                        daftarBarangHtml += '<td style="padding: 8px; word-wrap: break-word;">' + $('<div>').text(barang.nama).html() + '</td>';
                        daftarBarangHtml += '<td style="padding: 8px; text-align: center;">' + $('<div>').text(barang.jumlah || '-').html() + '</td>';
                        daftarBarangHtml += '<td style="padding: 8px; text-align: center;">' + $('<div>').text(barang.satuan || '-').html() + '</td>';
                        daftarBarangHtml += '</tr>';
                    }
                });
                daftarBarangHtml += '</tbody></table></div>';
            } else {
                daftarBarangHtml = '<p class="text-muted mb-0">Tidak ada data barang</p>';
            }
            $('#view_daftar_barang').html(daftarBarangHtml);

            // Tampilkan alasan penolakan jika status Ditolak Atasan
            if (status === 'Ditolak Atasan' && catatan && catatan.trim() !== '') {
                var catatanHtml = $('<div>').text(catatan).html().replace(/\n/g, '<br>');
                $('#viewCatatan').html(catatanHtml);
                $('#viewCatatanGroup').show();
            } else {
                $('#viewCatatanGroup').hide();
            }

            $('#viewPermintaanModal').modal('show');
        });

        // Handler untuk tombol delete
        $(document).on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            var noPermintaan = $(this).data('no-permintaan') || '-';
            
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Permintaan barang ' + noPermintaan + ' akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/logistik/permintaan-barang/' + id,
                        type: 'DELETE',
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
                            var message = 'Gagal menghapus data';
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
</script>
@endsection
