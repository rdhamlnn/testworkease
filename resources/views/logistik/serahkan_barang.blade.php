@extends('logistik.master')

@section('title', 'Serahkan Barang')

@section('styles')
<style>
    .table-responsive {
        display: block !important;
        width: 100%;
        overflow-x: auto !important;
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

    /* Fix font size untuk tabel */
    #serahkanBarangTable {
        font-size: 14px !important;
    }

    #serahkanBarangTable th,
    #serahkanBarangTable td {
        font-size: 14px !important;
        padding: 8px 12px !important;
    }

    #serahkanBarangTable .btn-sm {
        font-size: 12px !important;
        padding: 4px 8px !important;
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

    /* Button Serahkan - ukuran sama dengan btn-view */
    .btn-serahkan {
        min-width: 32px;
        padding: 4px 8px;
    }

    .btn-serahkan i {
        font-size: 14px;
    }

    /* Modal styling */
    #viewPermintaanModal .modal-header {
        background-color: #1B3C88 !important;
        color: #fff !important;
    }

    #viewPermintaanModal .modal-header .close {
        color: #fff !important;
        opacity: 1 !important;
    }

    #viewPermintaanModal .modal-header .close:hover {
        opacity: 0.8 !important;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Serahkan Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('logistik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Serahkan Barang</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Barang yang Perlu Diserahkan</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="serahkanBarangTable">
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
                                                            <div class="d-flex gap-2">
                                                                <button type="button" class="btn btn-info btn-sm btn-view" 
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
                                                                <form action="{{ route('logistik.serahkan-barang.proses', $pb->id_permintaan_barang) }}" method="POST" class="confirm-form no-transition" style="display:inline;" 
                                                                    data-message="Yakin ingin menyerahkan barang ini ke divisi?"
                                                                    data-button-text="Ya, Serahkan"
                                                                    data-button-class="btn-primary">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary btn-sm btn-serahkan" title="Serahkan ke Divisi">
                                                                        <i class="fas fa-share"></i>
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
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-info btn-sm btn-view" 
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
                                                        <form action="{{ route('logistik.serahkan-barang.proses', $pb->id_permintaan_barang) }}" method="POST" class="confirm-form no-transition" style="display:inline;" 
                                                            onsubmit="return false;"
                                                            data-message="Yakin ingin menyerahkan barang ini ke divisi?"
                                                            data-button-text="Ya, Serahkan"
                                                            data-button-class="btn-primary">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary btn-sm" title="Serahkan ke Divisi">
                                                                <i class="fas fa-hand-holding"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted">Belum ada barang yang perlu diserahkan</td>
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

@include('components.confirm-modal')
@endsection

@section('scripts')
<script>
    // Handler form submit dengan vanilla JS - langsung proses via AJAX
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form && form.tagName === 'FORM' && form.classList.contains('confirm-form')) {
            e.preventDefault();
            e.stopPropagation();
            
            const url = form.getAttribute('action');
            const message = form.dataset.message || 'Yakin ingin melanjutkan?';
            const description = form.dataset.description || '';
            const buttonText = form.dataset.buttonText || 'Ya, Lanjutkan';
            const buttonClass = form.dataset.buttonClass || 'btn-primary';
            
            // Jika showConfirmModal tersedia, gunakan itu
            if (typeof showConfirmModal === 'function') {
                showConfirmModal(url, message, description, buttonText, buttonClass);
            } else {
                // Fallback: confirm dialog + AJAX
                if (confirm(message)) {
                    const btn = form.querySelector('button[type="submit"]');
                    const originalHtml = btn ? btn.innerHTML : '';
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    }
                    
                    const formData = new FormData(form);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                      form.querySelector('input[name="_token"]')?.value;
                    
                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.redirect) {
                            window.location.href = data.redirect;
                        } else if (data.success) {
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal memproses');
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = originalHtml;
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Error:', err);
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                    });
                }
            }
            return false;
        }
    }, true);

    // Tunggu jQuery tersedia
    (function waitForJQuery() {
        if (typeof jQuery === 'undefined') {
            setTimeout(waitForJQuery, 100);
            return;
        }
        
        $(document).ready(function() {
            console.log('=== SERAHKAN BARANG PAGE LOADED ===');
            
            // Initialize DataTable
            try {
                if ($('#serahkanBarangTable').length > 0) {
                    if ($.fn.DataTable.isDataTable('#serahkanBarangTable')) {
                        $('#serahkanBarangTable').DataTable().destroy();
                    }
                    $('#serahkanBarangTable').DataTable({
                        "responsive": false,
                        "scrollX": false,
                        "autoWidth": false,
                        "pageLength": 10,
                        "language": {
                            "search": "Cari:",
                            "lengthMenu": "Tampilkan _MENU_ data per halaman",
                            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                            "paginate": { "next": "Selanjutnya", "previous": "Sebelumnya" }
                        }
                    });
                }
            } catch (error) {
                console.error('Error initializing DataTable:', error);
            }

            // Auto hide alerts
            setTimeout(function() { $('.alert').fadeOut(); }, 3000);
            
            // ========== HANDLER TOMBOL LIHAT DETAIL ==========
            $(document).on('click', '.btn-view', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('BTN-VIEW CLICKED');
                
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
                        daftarBarangAttr = $('<div>').html(daftarBarangAttr).text();
                        daftarBarangData = JSON.parse(daftarBarangAttr);
                    }
                } catch(err) {
                    console.error('Error parsing daftar barang:', err);
                }

                // Set data ke modal
                $('#viewNoPermintaan').text(noPermintaan);
                $('#viewNoWo').text(noWo);
                $('#viewTanggal').text(tanggal);
                $('#viewTotalHarga').text(totalHarga);
                
                // Set status badge
                var badgeClass = 'badge-info';
                if (status === 'Diterima Logistik' || status === 'Diserahkan ke Divisi') {
                    badgeClass = 'badge-success';
                } else if (status.indexOf('Ditolak') >= 0) {
                    badgeClass = 'badge-danger';
                } else if (status.indexOf('Menunggu') >= 0) {
                    badgeClass = 'badge-warning';
                }
                $('#viewStatus').html('<span class="badge ' + badgeClass + '">' + status + '</span>');
                
                // Build tabel daftar barang
                var html = '';
                if (Array.isArray(daftarBarangData) && daftarBarangData.length > 0) {
                    html = '<table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr><th>Nama Barang</th><th class="text-center">Jumlah</th><th class="text-center">Satuan</th></tr></thead><tbody>';
                    daftarBarangData.forEach(function(b) {
                        html += '<tr><td>' + (b.nama || '-') + '</td><td class="text-center">' + (b.jumlah || '-') + '</td><td class="text-center">' + (b.satuan || '-') + '</td></tr>';
                    });
                    html += '</tbody></table>';
                } else {
                    html = '<p class="text-muted mb-0">Tidak ada data barang</p>';
                }
                $('#view_daftar_barang').html(html);

                // Alasan penolakan
                if (status.indexOf('Ditolak') >= 0 && catatan) {
                    $('#viewCatatan').html(catatan);
                    $('#viewCatatanGroup').show();
                } else {
                    $('#viewCatatanGroup').hide();
                }

                // Tampilkan modal
                $('#viewPermintaanModal').modal('show');
            });
            
            // ========== HANDLER FORM CONFIRM ==========
            $(document).on('submit', '.confirm-form', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const form = $(this);
                const url = form.attr('action');
                const message = form.data('message') || 'Yakin?';
                
                if (typeof showConfirmModal === 'function') {
                    showConfirmModal(url, message, form.data('description') || '', form.data('button-text') || 'OK', form.data('button-class') || 'btn-primary');
                } else if (confirm(message)) {
                    const btn = form.find('button[type="submit"]');
                    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    
                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json' },
                        data: form.serialize(),
                        success: function(r) {
                            if (r.success && r.redirect) window.location.href = r.redirect;
                            else window.location.reload();
                        },
                        error: function() { alert('Error'); btn.prop('disabled', false); }
                    });
                }
                return false;
            });
        });
    })();
</script>
@endsection

