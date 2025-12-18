@extends('admin.master')

@section('title', 'Laporan Harian Mekanik')

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
        <h1>Laporan Harian Mekanik</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Laporan Harian Mekanik</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <!-- Laporan Harian Mekanik -->
        <div class="card">
            <div class="card-header">
                <h4>Daftar Laporan Harian Mekanik</h4>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>Tahun:</label>
                            <select class="form-control" id="filter-tahun">
                                <option value="">-- Semua --</option>
                                @foreach($tahunOptions as $tahun)
                                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Bulan:</label>
                            <select class="form-control" id="filter-bulan">
                                <option value="" selected>-- Semua --</option>
                                @foreach($bulanOptions as $key => $bulan)
                                    <option value="{{ str_pad($key, 2, '0', STR_PAD_LEFT) }}">{{ $bulan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Minggu:</label>
                            <select class="form-control" id="filter-minggu">
                                <option value="" selected>-- Semua --</option>
                                <option value="1">Minggu 1</option>
                                <option value="2">Minggu 2</option>
                                <option value="3">Minggu 3</option>
                                <option value="4">Minggu 4</option>
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
                                <th>Hari/Tanggal</th>
                                <th>Nama Unit</th>
                                <th>Keluhan/Kerusakan</th>
                                <th>Penyebab</th>
                                <th>Hari/Tanggal Mulai</th>
                                <th>Hari/Tanggal Selesai</th>
                                <th>Tindakan Perbaikan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $i => $laporan)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($laporan->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                <td>{{ $laporan->nama_unit }}</td>
                                <td>{{ $laporan->keluhan_kerusakan }}</td>
                                <td>{{ $laporan->penyebab_kerusakan }}</td>
                                <td>{{ \Carbon\Carbon::parse($laporan->tanggal_mulai)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                <td>{{ \Carbon\Carbon::parse($laporan->tanggal_selesai)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                <td>{{ $laporan->tindakan_perbaikan }}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm btn-view" 
                                            data-id="{{ $laporan->id_laporan_harian_mekanik }}" 
                                            data-toggle="modal" data-target="#viewLaporanModal" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-left">Tidak ada data laporan harian mekanik</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
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
<div class="modal fade" id="viewLaporanModal" tabindex="-1" role="dialog" aria-labelledby="viewLaporanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLaporanModalLabel">Detail Laporan Harian Mekanik</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Tanggal:</label>
                            <div id="view_tanggal" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Unit:</label>
                            <div id="view_nama_unit" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Keluhan/Kerusakan:</label>
                    <div id="view_keluhan_kerusakan" class="form-control-plaintext border p-2 rounded"></div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Penyebab Kerusakan:</label>
                    <div id="view_penyebab_kerusakan" class="form-control-plaintext border p-2 rounded"></div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Tanggal Mulai:</label>
                            <div id="view_tanggal_mulai" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Tanggal Selesai:</label>
                            <div id="view_tanggal_selesai" class="form-control-plaintext border p-2 rounded"></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Tindakan Perbaikan:</label>
                    <div id="view_tindakan_perbaikan" class="form-control-plaintext border p-2 rounded"></div>
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
        var tahun = $('#filter-tahun').val();
        var bulan = $('#filter-bulan').val();
        var minggu = $('#filter-minggu').val();
        
        // Show loading
        $('#laporanTable tbody').html('<tr><td><i class="fas fa-spinner fa-spin"></i> Memfilter data...</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>');
        
        // Prepare filter data
        var filterData = {
            _token: '{{ csrf_token() }}'
        };
        
        if (tahun && tahun !== '') {
            filterData.tahun = tahun;
        }
        if (bulan && bulan !== '') {
            filterData.bulan = bulan;
        }
        if (minggu && minggu !== '' && minggu !== 'semua') {
            filterData.minggu = minggu;
        }
        
        // Make AJAX call to filter data
        $.ajax({
            url: '{{ route("admin.laporan.harian-mekanik.filter") }}',
            type: 'POST',
            data: filterData,
            success: function(response) {
                if (response.success) {
                    updateTable(response.data);
                } else {
                    alert('Gagal memfilter data');
                }
            },
            error: function() {
                alert('Gagal memfilter data');
                $('#laporanTable tbody').html('<tr><td>Tidak ada data laporan harian mekanik</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>');
            }
        });
    }

    // Functions removed - no auto-selection of filters

    // Update table with filtered data
    function updateTable(data) {
        // Clear existing data from DataTable
        if (table) {
            table.clear();
            
            // Add new data to DataTable
            if (data.length > 0) {
                data.forEach(function(item, index) {
                    var row = [
                        (index + 1),
                        formatDate(item.tanggal),
                        (item.unit ? item.unit.nama_unit : '-'),
                        (item.keluhan_kerusakan || '-'),
                        (item.penyebab_kerusakan || '-'),
                        formatDate(item.tanggal_mulai),
                        formatDate(item.tanggal_selesai),
                        (item.tindakan_perbaikan || '-'),
                        '<button type="button" class="btn btn-info btn-sm btn-view" ' +
                        'data-id="' + item.id_laporan_harian_mekanik + '" ' +
                        'data-toggle="modal" data-target="#viewLaporanModal">' +
                        '<i class="fas fa-eye"></i>' +
                        '</button>'
                    ];
                    table.row.add(row);
                });
            } else {
                var emptyRow = [
                    'Tidak ada data laporan harian mekanik', '', '', '', '', '', '', '', ''
                ];
                table.row.add(emptyRow);
            }
            
            // Draw the table with new data
            table.draw();
        }
    }

    // Format date function
    function formatDate(dateString) {
        if (!dateString) return '-';
        try {
            // Handle date string format (YYYY-MM-DD)
            var date = new Date(dateString + 'T00:00:00');
            if (isNaN(date.getTime())) {
                return dateString; // Return original if invalid
            }
            
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                         'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            var dayName = days[date.getDay()];
            var day = String(date.getDate()).padStart(2, '0');
            var month = months[date.getMonth()];
            var year = date.getFullYear();
            
            return dayName + ', ' + day + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + year;
        } catch (e) {
            return dateString; // Return original if error
        }
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
                    "emptyTable": "Tidak ada data laporan harian mekanik"
                },
                "drawCallback": function(settings) {
                    // Force empty table message to left align in first column
                    $('.dataTables_empty').css({
                        'text-align': 'left !important',
                        'padding-left': '15px !important',
                        'padding-right': '0 !important',
                        'margin': '0 !important',
                        'float': 'none !important',
                        'position': 'static !important',
                        'direction': 'ltr !important',
                        'width': 'auto !important'
                    });
                }
            });

        // Show all data by default - no auto-filtering on page load
        // Only filter when user manually changes dropdown values

        // Auto filter when dropdowns change
        $('#filter-tahun, #filter-bulan, #filter-minggu').on('change', function() {
            filterLaporan();
        });

        // View detail modal
        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            
            $.ajax({
                url: '/admin/laporan-harian-mekanik/' + id,
                type: 'GET',
                success: function(data) {
                    $('#view_tanggal').text(formatDate(data.tanggal));
                    $('#view_nama_unit').text(data.nama_unit || '-');
                    $('#view_keluhan_kerusakan').text(data.keluhan_kerusakan || '-');
                    $('#view_penyebab_kerusakan').text(data.penyebab_kerusakan || '-');
                    $('#view_tanggal_mulai').text(formatDate(data.tanggal_mulai));
                    $('#view_tanggal_selesai').text(formatDate(data.tanggal_selesai));
                    $('#view_tindakan_perbaikan').text(data.tindakan_perbaikan || '-');
                    
                    $('#viewLaporanModal').modal('show');
                },
                error: function() {
                    alert('Gagal mengambil data laporan');
                }
            });
        });
    });
</script>
@endsection
