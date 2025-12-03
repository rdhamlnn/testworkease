@extends('kadivproduksi.master')

@section('title', 'Riwayat Work Order')

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
    
    #view_barang_table table th:nth-child(3),
    #view_barang_table table td:nth-child(3) {
        text-align: center !important;
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
        <h1>Riwayat Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('kadivproduksi.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Riwayat Work Order</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Riwayat Work Order</h4>
                        <div class="card-header-action">
                            <select id="filterStatus" class="form-control" style="width: auto; display: inline-block;">
                                <option value="">Semua Status</option>
                                <option value="Disetujui">Disetujui/Selesai</option>
                                <option value="Ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="riwayatWorkOrderTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No Work-Order</th>
                                        <th>Jenis WO</th>
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
                                        <tr>
                                            <td></td>
                                            <td>{{ $wo->no_surat_pengajuan }}</td>
                                            <td>
                                                @if($wo->jenisWorkOrder)
                                                    {{ $wo->jenisWorkOrder->nama_jenis_wo }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $wo->divisi_pengaju }}</td>
                                            <td>{{ $wo->ditujukan }}</td>
                                            <td data-order="{{ \Carbon\Carbon::parse($wo->tanggal)->format('Y-m-d') }}">{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                            <td>{{ $wo->unit_code ?? $wo->unit }}</td>
                                            <td>{{ Str::limit($wo->uraian, 30) }}</td>
                                            <td>
                                                @php
                                                    $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                                                @endphp
                                                @if($status == 'Disetujui' || $status == 'Selesai')
                                                    <span class="badge badge-success">{{ $status }}</span>
                                                @elseif($status == 'Ditolak')
                                                    <span class="badge badge-danger">{{ $status }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ $status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm btn-icon btn-view" 
                                                    data-id="{{ $wo->id_surat_pengajuan }}" data-toggle="modal" data-target="#viewWorkOrderModal" title="Lihat Detail">
                                                    <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" alt="view">
                                                </button>
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
                            <label><strong>Jenis Work Order:</strong></label>
                            <p id="view_jenis_wo" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
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
        var table = $('#riwayatWorkOrderTable').DataTable({
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "pageLength": 10,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "order": [[5, "desc"]],
            "columnDefs": [
                {
                    "orderable": false,
                    "targets": 0
                }
            ],
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
            },
            "drawCallback": function(settings) {
                var api = this.api();
                var start = api.page.info().start;
                
                // Update nomor urut berdasarkan urutan sorting dan pagination
                api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = start + i + 1;
                });
            }
        });

        // Filter berdasarkan status
        $('#filterStatus').on('change', function() {
            var status = $(this).val();
            if (status === '') {
                table.column(9).search('').draw(); // Kolom Status (index 9 setelah penambahan kolom Jenis WO)
            } else if (status === 'Disetujui') {
                // Filter untuk Disetujui atau Selesai
                table.column(9).search('^(Disetujui|Selesai)$', true, false).draw();
            } else {
                // Exact match untuk Ditolak
                table.column(9).search('^' + status + '$', true, false).draw();
            }
        });

        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            viewWorkOrder(id);
        });
    });

    function viewWorkOrder(id) {
        fetch(`/kadivproduksi/work-order/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#view_no_wo').text(data.no_work_order);
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#view_divisi_pengaju').text(data.divisi_pengaju);
                $('#view_ditujukan').text(data.ditujukan);
                
                // Cek jenis work order
                const jenisWo = data.jenis_wo ? data.jenis_wo.toLowerCase() : '';
                const isPembelian = jenisWo === 'pembelian';
                
                // Parse format dengan qty dan tampilkan dalam table
                let unitDisplay = '-';
                let barangTable = '';
                
                if (isPembelian) {
                    // Jika jenis WO adalah Pembelian, tampilkan tabel di div baru
                    if (Array.isArray(data.unit)) {
                        // Jika array, buat table dengan qty
                        if (data.unit.length > 0) {
                            barangTable = '<table class="table table-bordered table-sm mb-0" style="width: 100%;">';
                            barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
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
                                barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                                parts.forEach(function(part, index) {
                                    const qtyMatch = part.match(/\(qty:\s*(\d+)\)/);
                                    if (qtyMatch) {
                                        const qty = qtyMatch[1];
                                        const barangName = part.replace(/\s*\(qty:\s*\d+\)/, '').trim();
                                        barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${barangName}</td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                                    } else {
                                        barangTable += `<tr><td style="width: 8%;">${index + 1}</td><td style="width: 42%;">${part}</td>></td><td style="width: 25%;" class="text-center"><strong>-</strong></td></tr>`;
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
                                barangTable += '<thead><tr><th style="width: 8%;">No</th><th style="width: 42%;">Nama Barang</th><th style="width: 25%;">Qty</th></tr></thead><tbody>';
                                barangTable += `<tr><td style="width: 8%;">1</td><td style="width: 42%;">${barangName}</td><td style="width: 25%;"></td><td style="width: 25%;" class="text-center"><strong>${qty}</strong></td></tr>`;
                                barangTable += '</tbody></table>';
                            }
                        }
                    }
                    
                    // Tampilkan tabel di div baru dan sembunyikan field Unit
                    if (barangTable) {
                        $('#view_barang_table').html(barangTable);
                        $('#view_barang_container').show();
                    } else {
                        $('#view_barang_container').hide();
                    }
                    $('#view_label_unit').closest('.form-group').hide();
                } else {
                    // Jika bukan Pembelian, tampilkan di field Unit seperti biasa
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
                    $('#view_unit').html(unitDisplay);
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
                
                $('#view_jenis_wo').text(data.jenis_wo || '-');
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
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data work order');
            });
    }

    // View Dokumentasi (dari modal view work order - tutup modal detail dulu, lalu buka modal foto)
    $(document).on('click', '.btn-view-dokumentasi-modal', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fotoUrl = $(this).data('foto');
        const namaWo = $(this).data('nama');
        
        // Set data foto
        $('#dokumentasiViewKadiv').attr('src', fotoUrl);
        $('#dokumentasiViewKadiv').attr('alt', 'Dokumentasi ' + namaWo);
        $('#namaWoViewKadiv').text('Dokumentasi Work Order: ' + namaWo);
        
        // Tutup modal work order terlebih dahulu
        $('#viewWorkOrderModal').modal('hide');
        
        // Setelah modal work order tertutup, buka modal dokumentasi
        $('#viewWorkOrderModal').on('hidden.bs.modal', function() {
            $('#modalViewDokumentasiKadiv').modal('show');
            // Hapus event listener setelah digunakan
            $('#viewWorkOrderModal').off('hidden.bs.modal');
        });
    });
</script>

<!-- Modal View Dokumentasi -->
<div class="modal fade" id="modalViewDokumentasiKadiv" tabindex="-1" role="dialog" aria-labelledby="modalViewDokumentasiKadivLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewDokumentasiKadivLabel">Dokumentasi Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="dokumentasiViewKadiv" src="" alt="Dokumentasi" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0" id="namaWoViewKadiv"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

