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
    #viewWorkOrderModal .modal-body .table-responsive {
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch;
        margin: 0 !important;
    }

    #viewWorkOrderModal .modal-body table {
        width: 100% !important;
        max-width: 100% !important;
        min-width: auto !important;
        table-layout: auto !important;
        margin-bottom: 0 !important;
    }

    #viewWorkOrderModal .modal-body table th,
    #viewWorkOrderModal .modal-body table td {
        white-space: normal !important;
        word-wrap: break-word !important;
        padding: 8px !important;
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
                                        @endphp
                                        @if($daftarBarang->count() > 0)
                                            @foreach($daftarBarang as $idx => $barang)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $pb->no_permintaan_barang }}</td>
                                                    <td>{{ $pb->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
                                                    <td>{{ $barang->nama_barang ?? '-' }}</td>
                                                    <td class="text-center">{{ $barang->jumlah ?? '-' }}</td>
                                                    <td class="text-center">{{ $barang->satuan ?? '-' }}</td>
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
                                                                data-id="{{ $pb->id_surat_pengajuan }}" 
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
                                            @endforeach
                                        @else
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $pb->no_permintaan_barang }}</td>
                                                <td>{{ $pb->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
                                                <td class="text-muted">-</td>
                                                <td class="text-center text-muted">-</td>
                                                <td class="text-center text-muted">-</td>
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
                                                            data-id="{{ $pb->id_surat_pengajuan }}" 
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
                            <label><strong>Jenis Work Order:</strong></label>
                            <p id="view_jenis_wo" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group" id="view_unit_container">
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
                <div class="form-group" id="view_harga_barang_container" style="display: none;">
                    <label><strong>Detail Harga Barang:</strong></label>
                    <div id="view_harga_barang" class="form-control-plaintext border p-2 rounded"></div>
                </div>
                <div class="form-group" id="view_catatan_penolakan_container" style="display: none;">
                    <label><strong>Alasan Penolakan:</strong></label>
                    <p id="view_catatan_penolakan" class="form-control-plaintext border p-2 rounded text-danger"></p>
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
            console.log('Viewing Work Order ID:', id);
            if(id) {
                viewWorkOrder(id);
            } else {
                alert('ID Work Order tidak ditemukan');
            }
        });
    });

    function viewWorkOrder(id) {
        // Show modal immediately
        $('#viewWorkOrderModal').modal('show');
        
        // Clear previous content and show loading
        $('#view_no_wo, #view_tanggal, #view_divisi_pengaju, #view_ditujukan, #view_jenis_wo, #view_unit, #view_uraian').text('Memuat...');
        $('#view_barang_container, #view_harga_barang_container, #view_catatan_penolakan_container').hide();
        $('#view_dokumentasi').html('<span class="text-muted">Memuat...</span>');
        
        fetch(`{{ url('purchasing/api/work-order') }}/${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Gagal mengambil data (Status: ' + response.status + ')');
                return response.json();
            })
            .then(data => {
                try {
                    $('#view_no_wo').text(data.no_surat_pengajuan || data.no_work_order || '-');
                    
                    // Safe date parsing
                    if (data.tanggal) {
                        try {
                            const dateObj = new Date(data.tanggal);
                            if (isNaN(dateObj.getTime())) {
                                $('#view_tanggal').text(data.tanggal);
                            } else {
                                $('#view_tanggal').text(dateObj.toLocaleDateString('id-ID', { 
                                    weekday: 'long', 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                }));
                            }
                        } catch (e) {
                            $('#view_tanggal').text(data.tanggal);
                        }
                    } else {
                        $('#view_tanggal').text('-');
                    }

                    $('#view_divisi_pengaju').text(data.divisi_pengaju || '-');
                    $('#view_ditujukan').text(data.ditujukan || '-');
                    $('#view_jenis_wo').text(data.jenis_wo || '-');
                    
                    const jenisWo = data.jenis_wo ? data.jenis_wo.toLowerCase() : '';
                    const isPembelian = jenisWo === 'pembelian' || jenisWo.includes('pembelian');
                    
                    // Helper function for satuan
                    function getSatuanByBarangName(barangName) {
                        if (data.satuan_lookup && data.satuan_lookup[barangName]) {
                            return data.satuan_lookup[barangName];
                        }
                        return '-';
                    }
                    
                    if (isPembelian) {
                        let barangTable = '';
                        let displayItems = [];
                        
                        if (Array.isArray(data.unit)) {
                            displayItems = data.unit;
                        } else if (data.unit && typeof data.unit === 'string') {
                            displayItems = data.unit.split(',').map(v => v.trim()).filter(v => v);
                        }
                        
                        if (displayItems.length > 0) {
                            barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                            barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 37%;">Nama Barang</th><th style="width: 15%; text-align: center !important;">Qty</th><th style="width: 20%; text-align: center !important;">Satuan</th></tr></thead><tbody>';
                            
                            displayItems.forEach(function(item, index) {
                                const qtyMatch = item.match(/\(qty:\s*(\d+)\)/);
                                let qty = '-';
                                let barangName = item;
                                
                                if (qtyMatch) {
                                    qty = qtyMatch[1];
                                    barangName = item.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                }
                                
                                const satuan = getSatuanByBarangName(barangName);
                                barangTable += `<tr><td>${index + 1}</td><td>${barangName}</td><td class="text-center"><strong>${qty}</strong></td><td class="text-center">${satuan}</td></tr>`;
                            });
                            
                            barangTable += '</tbody></table>';
                            $('#view_barang_table').html(barangTable);
                            $('#view_barang_container').show();
                            $('#view_unit_container').hide();
                        } else {
                            $('#view_unit').text(data.unit || '-');
                            $('#view_unit_container').show();
                            $('#view_barang_container').hide();
                        }
                    } else {
                        let unitDisplay = data.unit || '-';
                        if (typeof data.unit === 'string') {
                            unitDisplay = data.unit.replace(/\s*\(qty:\s*\d+\)/g, '');
                        }
                        $('#view_unit').text(unitDisplay);
                        $('#view_unit_container').show();
                        $('#view_barang_container').hide();
                    }
                    
                    $('#view_uraian').text(data.uraian || '-');
                    
                    // Pricing details
                    if (data.harga_barang && Array.isArray(data.harga_barang) && data.harga_barang.length > 0) {
                        let hargaHtml = '<table class="table table-sm table-bordered mb-0"><thead><tr><th>Barang</th><th>Harga</th></tr></thead><tbody>';
                        data.harga_barang.forEach(function(item) {
                            hargaHtml += `<tr><td>${item.nama_barang || '-'}</td><td>Rp ${new Intl.NumberFormat('id-ID').format(item.harga || 0)}</td></tr>`;
                        });
                        hargaHtml += '</tbody></table>';
                        $('#view_harga_barang').html(hargaHtml);
                        $('#view_harga_barang_container').show();
                    } else {
                        $('#view_harga_barang_container').hide();
                    }
                    
                    // Rejection note
                    if (data.catatan_penolakan) {
                        $('#view_catatan_penolakan').text(data.catatan_penolakan);
                        $('#view_catatan_penolakan_container').show();
                    } else {
                        $('#view_catatan_penolakan_container').hide();
                    }
                    
                    // Documentation
                    if (data.dokumentasi && data.dokumentasi !== '-') {
                        const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                        const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                        if (isImage) {
                            $('#view_dokumentasi').html(`<img src="/storage/${data.dokumentasi}" alt="Dokumentasi" style="max-width: 100%; max-height: 300px; object-fit: contain; border-radius: 8px;">`);
                        } else {
                            $('#view_dokumentasi').html(`<a href="/storage/${data.dokumentasi}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fas fa-file"></i> Lihat Dokumentasi</a>`);
                        }
                    } else {
                        $('#view_dokumentasi').html('<span class="text-muted">-</span>');
                    }
                    
                    // Ensure modal is shown (in case it wasn't opened by data-toggle)
                    $('#viewWorkOrderModal').modal('show');
                } catch (err) {
                    console.error('Error populating modal:', err);
                    alert('Terjadi kesalahan saat memproses data work order.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data work order: ' + error.message);
                $('#viewWorkOrderModal').modal('hide');
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
