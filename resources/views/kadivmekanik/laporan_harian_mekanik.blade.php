@extends('kadivmekanik.master')

@section('title', 'Laporan Harian Mekanik')

@section('styles')
<style>
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

    .table th,
    .table td {
        text-align: left !important;
        vertical-align: middle;
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
            <div class="breadcrumb-item"><a href="{{ route('kadivmekanik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Laporan Harian Mekanik</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <!-- Laporan Harian Mekanik -->
        <div class="card">
            <div class="card-header">
                <h4>Daftar Laporan Harian Mekanik</h4>
                <div class="card-header-action">
                    <div style="display: flex; gap: 10px;">
                        <a href="#" id="preview-btn" class="btn btn-info">
                            <i class="fas fa-eye"></i> Preview
                        </a>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#tambahLaporanModal">
                            <i class="fas fa-plus"></i> Tambah Laporan
                        </button>
                    </div>
                </div>
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
                                <td data-order="{{ $i + 1 }}">{{ $i + 1 }}</td>
                                <td data-order="{{ \Carbon\Carbon::parse($laporan->tanggal)->format('Ymd') }}">{{ \Carbon\Carbon::parse($laporan->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                <td>{{ $laporan->nama_unit }}</td>
                                <td>{{ $laporan->keluhan_kerusakan }}</td>
                                <td>{{ $laporan->penyebab_kerusakan }}</td>
                                <td data-order="{{ \Carbon\Carbon::parse($laporan->tanggal_mulai)->format('Ymd') }}">{{ \Carbon\Carbon::parse($laporan->tanggal_mulai)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                <td data-order="{{ \Carbon\Carbon::parse($laporan->tanggal_selesai)->format('Ymd') }}">{{ \Carbon\Carbon::parse($laporan->tanggal_selesai)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                <td>{{ $laporan->tindakan_perbaikan }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-info btn-sm btn-view" 
                                            data-id="{{ $laporan->id_laporan_harian_mekanik }}" data-toggle="modal" data-target="#viewLaporanModal" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm btn-edit" 
                                            data-id="{{ $laporan->id_laporan_harian_mekanik }}" data-toggle="modal" data-target="#editLaporanModal" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm btn-delete" 
                                            data-url="{{ route('kadivmekanik.laporan-harian-mekanik.destroy', $laporan->id_laporan_harian_mekanik) }}"
                                            data-message="Yakin ingin menghapus laporan ini?" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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

<!-- Modal Tambah Laporan Harian Mekanik -->
<div class="modal fade" id="tambahLaporanModal" tabindex="-1" role="dialog" aria-labelledby="tambahLaporanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahLaporanModalLabel">Tambah Laporan Harian Mekanik</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('kadivmekanik.laporan.harian-mekanik.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal">Hari/Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama_unit">Nama Unit <span class="text-danger">*</span></label>
                                <select class="form-control" id="nama_unit" name="nama_unit" required>
                                    <option value="">-- Pilih Unit --</option>
                                    @foreach($unitOptions as $unit)
                                        <option value="{{ $unit->nama_unit }}">{{ $unit->nama_unit }} ({{ $unit->kode_unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="keluhan_kerusakan">Keluhan/Kerusakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keluhan_kerusakan" name="keluhan_kerusakan" rows="3" required placeholder="Masukkan keluhan atau kerusakan yang ditemukan"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="penyebab_kerusakan">Penyebab Kerusakan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="penyebab_kerusakan" name="penyebab_kerusakan" rows="3" required placeholder="Masukkan penyebab kerusakan yang ditemukan"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_mulai">Hari/Tanggal Mulai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal_selesai">Hari/Tanggal Selesai <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tindakan_perbaikan">Tindakan Perbaikan <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="tindakan_perbaikan" name="tindakan_perbaikan" rows="3" required placeholder="Masukkan tindakan perbaikan yang dilakukan"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="resetKadivLaporanHarianBtn">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
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
    // Helper function to get current filter parameters including sort and search from DataTable
    function getCurrentFilterParams() {
        var params = [];
        var tahun = $('#filter-tahun').val();
        var bulan = $('#filter-bulan').val();
        var minggu = $('#filter-minggu').val();
        
        if (tahun && tahun !== '') {
            params.push('tahun=' + encodeURIComponent(tahun));
        }
        if (bulan && bulan !== '') {
            params.push('bulan=' + encodeURIComponent(bulan));
        }
        if (minggu && minggu !== '' && minggu !== 'semua') {
            params.push('minggu=' + encodeURIComponent(minggu));
        }
        
        // Get DataTable sort order
        if (table) {
            var order = table.order();
            if (order && order.length > 0) {
                var columnIndex = order[0][0];
                var sortDirection = order[0][1];
                params.push('sort_by=' + encodeURIComponent(columnIndex));
                params.push('sort_order=' + encodeURIComponent(sortDirection));
            }
            
            // Get DataTable search query
            var searchQuery = table.search();
            if (searchQuery && searchQuery !== '') {
                params.push('search=' + encodeURIComponent(searchQuery));
            }
        }
        
        return params.length > 0 ? '?' + params.join('&') : '';
    }

    // Update preview link with current filters, sort, and search
    function updatePreviewLink() {
        var filterParams = getCurrentFilterParams();
        var baseUrl = '{{ route("kadivmekanik.laporan-harian-mekanik.preview") }}';
        var previewUrl = baseUrl + filterParams + (filterParams ? '&format=preview' : '?format=preview');
        $('#preview-btn').attr('href', previewUrl);
    }



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
        
        // Hanya kirim filter yang memiliki nilai
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
            url: '{{ route("kadivmekanik.laporan.harian-mekanik.filter") }}',
            type: 'POST',
            data: filterData,
            success: function(response) {
                if (response.success) {
                    updateTable(response.data);
                    updatePreviewLink(); // Update preview link
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
                        (item.nama_unit || '-'),
                        (item.keluhan_kerusakan || '-'),
                        (item.penyebab_kerusakan || '-'),
                        formatDate(item.tanggal_mulai),
                        formatDate(item.tanggal_selesai),
                        (item.tindakan_perbaikan || '-'),
                        '<div class="d-flex gap-2">' +
                            '<button type="button" class="btn btn-info btn-sm btn-view" ' +
                                'data-id="' + item.id_laporan_harian_mekanik + '" data-toggle="modal" data-target="#viewLaporanModal">' +
                                '<i class="fas fa-eye"></i>' +
                            '</button>' +
                            '<button type="button" class="btn btn-warning btn-sm btn-edit" ' +
                                'data-id="' + item.id_laporan_harian_mekanik + '" data-toggle="modal" data-target="#editLaporanModal">' +
                                '<i class="fas fa-edit"></i>' +
                            '</button>' +
                            (item.id_laporan_harian_mekanik ? 
                                '<button type="button" class="btn btn-danger btn-sm btn-delete" ' +
                                    'data-url="/kadivmekanik/laporan-harian-mekanik/' + item.id_laporan_harian_mekanik + '" ' +
                                    'data-message="Yakin ingin menghapus laporan ini?">' +
                                    '<i class="fas fa-trash"></i>' +
                                '</button>' 
                            : '') +
                        '</div>'
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
            "order": [[1, 'asc']], // Default sort by Hari/Tanggal ascending (oldest to newest)
            "columnDefs": [
                {
                    "targets": 0,
                    "orderable": false,
                    "searchable": false
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
                "emptyTable": "Tidak ada data laporan harian mekanik"
            }
        });

        // Auto-generate row numbers on every draw (always sequential 1, 2, 3...)
        table.on('order.dt search.dt draw.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
                cell.innerHTML = table.page.info().start + i + 1;
            });
        }).draw();

        // Show all data by default - no auto-filtering on page load
        // Only filter when user manually changes dropdown values
        $('#filter-tahun, #filter-bulan, #filter-minggu').on('change', function() {
            filterLaporan();
            updatePreviewLink(); // Update preview link when filters change
        });

        // Update preview link when DataTable sort or search changes
        table.on('order.dt', function() {
            updatePreviewLink();
        });
        table.on('search.dt', function() {
            updatePreviewLink();
        });

        // Initialize preview link
        updatePreviewLink();
        
        // Handle preview button click - open in new tab like Google Drive
        $('#preview-btn').on('click', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url) {
                // Open in new tab (like Google Drive)
                window.open(url, '_blank');
            }
        });

        // Set tanggal hari ini untuk form tambah
        var today = new Date().toISOString().split('T')[0];
        $('#tanggal').val(today);
        $('#tanggal_mulai').val(today);

        const tambahForm = $('#tambahLaporanModal form');
        const tanggalField = $('#tanggal');
        const tanggalMulaiField = $('#tanggal_mulai');

        $('#resetKadivLaporanHarianBtn').on('click', function () {
            const currentTanggal = tanggalField.val();
            const currentTanggalMulai = tanggalMulaiField.val();

            tambahForm.find(':input')
                .not(':button, :submit, :reset, :hidden')
                .each(function () {
                    if (this.id === 'tanggal' || this.id === 'tanggal_mulai') {
                        return;
                    }
                    if (this.type === 'checkbox' || this.type === 'radio') {
                        this.checked = false;
                    } else {
                        $(this).val('');
                    }
                });

            tanggalField.val(currentTanggal);
            tanggalMulaiField.val(currentTanggalMulai);
        });
    });

    // Event handler untuk button view
    $(document).on('click', '.btn-view', function() {
        var id = $(this).data('id');
        viewLaporan(id);
    });

    // Event handler untuk button edit
    $(document).on('click', '.btn-edit', function() {
        var id = $(this).data('id');
        editLaporan(id);
    });

    // View laporan harian mekanik
    function viewLaporan(id) {
        fetch(`/kadivmekanik/laporan-harian-mekanik/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#view_tanggal').text(formatDate(data.tanggal));
                $('#view_nama_unit').text(data.nama_unit);
                $('#view_keluhan_kerusakan').text(data.keluhan_kerusakan);
                $('#view_penyebab_kerusakan').text(data.penyebab_kerusakan);
                $('#view_tanggal_mulai').text(formatDate(data.tanggal_mulai));
                $('#view_tanggal_selesai').text(formatDate(data.tanggal_selesai));
                $('#view_tindakan_perbaikan').text(data.tindakan_perbaikan);
                $('#viewLaporanModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data laporan');
            });
    }

    // Edit laporan harian mekanik
    function editLaporan(id) {
        fetch(`/kadivmekanik/laporan-harian-mekanik/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#edit_laporan_id').val(data.id_laporan_harian_mekanik);
                $('#edit_tanggal').val(data.tanggal);
                $('#edit_nama_unit').val(data.nama_unit);
                $('#edit_keluhan_kerusakan').val(data.keluhan_kerusakan);
                $('#edit_penyebab_kerusakan').val(data.penyebab_kerusakan);
                $('#edit_tanggal_mulai').val(data.tanggal_mulai);
                $('#edit_tanggal_selesai').val(data.tanggal_selesai);
                $('#edit_tindakan_perbaikan').val(data.tindakan_perbaikan);
                
                // Set form action
                $('#editLaporanForm').attr('action', `/kadivmekanik/laporan-harian-mekanik/${data.id_laporan_harian_mekanik}`);
                
                $('#editLaporanModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data laporan');
            });
    }

</script>

<!-- Modal View Laporan Harian Mekanik -->
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
                            <label><strong>Hari/Tanggal:</strong></label>
                            <p id="view_tanggal" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Nama Unit:</strong></label>
                            <p id="view_nama_unit" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Keluhan/Kerusakan:</strong></label>
                    <p id="view_keluhan_kerusakan" class="form-control-plaintext border p-2 rounded"></p>
                </div>
                <div class="form-group">
                    <label><strong>Penyebab Kerusakan:</strong></label>
                    <p id="view_penyebab_kerusakan" class="form-control-plaintext border p-2 rounded"></p>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Hari/Tanggal Mulai:</strong></label>
                            <p id="view_tanggal_mulai" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Hari/Tanggal Selesai:</strong></label>
                            <p id="view_tanggal_selesai" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Tindakan Perbaikan:</strong></label>
                    <p id="view_tindakan_perbaikan" class="form-control-plaintext border p-2 rounded"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Laporan Harian Mekanik -->
<div class="modal fade" id="editLaporanModal" tabindex="-1" role="dialog" aria-labelledby="editLaporanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editLaporanModalLabel">Edit Laporan Harian Mekanik</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editLaporanForm" action="" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_laporan_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tanggal">Hari/Tanggal</label>
                                <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_nama_unit">Nama Unit</label>
                                <input type="text" class="form-control" id="edit_nama_unit" name="nama_unit" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_keluhan_kerusakan">Keluhan/Kerusakan</label>
                        <textarea class="form-control" id="edit_keluhan_kerusakan" name="keluhan_kerusakan" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_penyebab_kerusakan">Penyebab Kerusakan</label>
                        <textarea class="form-control" id="edit_penyebab_kerusakan" name="penyebab_kerusakan" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tanggal_mulai">Hari/Tanggal Mulai</label>
                                <input type="date" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tanggal_selesai">Hari/Tanggal Selesai</label>
                                <input type="date" class="form-control" id="edit_tanggal_selesai" name="tanggal_selesai" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_tindakan_perbaikan">Tindakan Perbaikan</label>
                        <textarea class="form-control" id="edit_tindakan_perbaikan" name="tindakan_perbaikan" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('components.delete-confirm-modal')
@endsection