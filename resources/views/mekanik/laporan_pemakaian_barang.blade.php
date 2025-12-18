@extends('mekanik.master')

@section('title', 'Laporan Pemakaian Barang')

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
        <h1>Laporan Pemakaian Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('mekanik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Laporan Pemakaian Barang</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <!-- Laporan Pemakaian Barang -->
        <div class="card">
            <div class="card-header">
                <h4>Daftar Laporan Pemakaian Barang</h4>
                <div class="card-header-action">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#tambahLaporanModal">
                        <i class="fas fa-plus"></i> Tambah Laporan
                    </button>
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
                                <th>Tanggal</th>
                                <th>Kode Unit</th>
                                <th>Sparepart/Material/Jasa</th>
                                <th>Jumlah</th>
                                <th>Bentuk Satuan</th>
                                <th>Harga Satuan</th>
                                <th>Total Harga</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $i => $laporan)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($laporan->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ $laporan->kode_unit }}</td>
                                <td>{{ $laporan->nama_barang }}</td>
                                <td>{{ $laporan->jumlah }}</td>
                                <td>{{ $laporan->bentuk_satuan }}</td>
                                <td>Rp {{ number_format($laporan->harga_satuan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($laporan->total_harga, 0, ',', '.') }}</td>
                                <td>{{ $laporan->keterangan ?: '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-info btn-sm btn-view" 
                                            data-id="{{ $laporan->id_laporan_pemakaian_barang }}" data-toggle="modal" data-target="#viewLaporanModal" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">Tidak ada data laporan pemakaian barang</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah Laporan Pemakaian Barang -->
<div class="modal fade" id="tambahLaporanModal" tabindex="-1" role="dialog" aria-labelledby="tambahLaporanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahLaporanModalLabel">Tambah Laporan Pemakaian Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('mekanik.laporan.pemakaian-barang.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kode_unit">Kode Unit <span class="text-danger">*</span></label>
                                <select class="form-control" id="kode_unit" name="kode_unit" required>
                                    <option value="">-- Pilih Kode Unit --</option>
                                    <option value="TC 01">TC 01</option>
                                    <option value="F 02">F 02</option>
                                    <option value="DA 8012">DA 8012</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="nama_barang">Sparepart/Material/Jasa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required placeholder="Masukkan nama sparepart, material, atau jasa">
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="jumlah">Jumlah <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah" name="jumlah" min="1" required placeholder="Masukkan jumlah">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bentuk_satuan">Bentuk Satuan <span class="text-danger">*</span></label>
                                <select class="form-control" id="bentuk_satuan" name="bentuk_satuan" required>
                                    <option value="">-- Pilih Satuan --</option>
                                    <option value="Pcs">Pcs</option>
                                    <option value="Kg">Kg</option>
                                    <option value="Liter">Liter</option>
                                    <option value="Meter">Meter</option>
                                    <option value="Set">Set</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="harga_satuan">Harga Satuan <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="harga_satuan" name="harga_satuan" min="0" step="0.01" required placeholder="Masukkan harga satuan">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="total_harga">Total Harga</label>
                                <input type="number" class="form-control" id="total_harga" name="total_harga" min="0" step="0.01" readonly required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" placeholder="Masukkan keterangan tambahan (opsional)">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="resetPemakaianBtn">Reset</button>
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
    function filterLaporan() {
        var tahun = $('#filter-tahun').val();
        var bulan = $('#filter-bulan').val();
        var minggu = $('#filter-minggu').val();
        
        // Show loading
        $('#laporanTable tbody').html('<tr><td><i class="fas fa-spinner fa-spin"></i> Memfilter data...</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>');
        
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
            url: '{{ route("mekanik.laporan.pemakaian-barang.filter") }}',
            type: 'POST',
            data: filterData,
            success: function(response) {
                if (response.success) {
                    updateTable(response.data);
                } else {
                    alert('Gagal memfilter data');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                alert('Gagal memfilter data: ' + error);
                $('#laporanTable tbody').html('<tr><td>Tidak ada data laporan pemakaian barang</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>');
            }
        });
    }

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
                        (item.kode_unit || '-'),
                        (item.nama_barang || '-'),
                        (item.jumlah || '-'),
                        (item.bentuk_satuan || '-'),
                        'Rp ' + formatNumber(item.harga_satuan),
                        'Rp ' + formatNumber(item.total_harga),
                        (item.keterangan || '-'),
                        '<div class="d-flex gap-2">' +
                            '<button type="button" class="btn btn-info btn-sm btn-view" ' +
                                'data-id="' + item.id_laporan_pemakaian_barang + '" data-toggle="modal" data-target="#viewLaporanModal">' +
                                '<i class="fas fa-eye"></i>' +
                            '</button>' +
                        '</div>'
                    ];
                    table.row.add(row);
                });
            } else {
                var emptyRow = [
                    'Tidak ada data laporan pemakaian barang', '', '', '', '', '', '', '', '', ''
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

    // Format number function
    function formatNumber(number) {
        if (!number) return '0';
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Calculate total harga
    function calculateTotal() {
        var jumlah = parseFloat($('#jumlah').val()) || 0;
        var hargaSatuan = parseFloat($('#harga_satuan').val()) || 0;
        var total = jumlah * hargaSatuan;
        $('#total_harga').val(total.toFixed(2));
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
                "emptyTable": "Tidak ada data laporan pemakaian barang"
            }
        });

        // Auto filter when dropdowns change
        $('#filter-tahun, #filter-bulan, #filter-minggu').on('change', function() {
            filterLaporan();
        });

        // Set tanggal hari ini untuk form tambah
        var today = new Date().toISOString().split('T')[0];
        $('#tanggal').val(today);

        const pemakaianForm = $('#tambahLaporanModal form');
        const tanggalBarangField = $('#tanggal');

        $('#tambahLaporanModal').on('shown.bs.modal', function () {
            if (!tanggalBarangField.val()) {
                tanggalBarangField.val(new Date().toISOString().split('T')[0]);
            }
            tanggalBarangField.data('default-value', tanggalBarangField.val());
        });

        $('#resetPemakaianBtn').on('click', function () {
            const currentTanggal = tanggalBarangField.val();

            pemakaianForm.find(':input')
                .not(':button, :submit, :reset, :hidden')
                .each(function () {
                    if (this.id === 'tanggal') {
                        return;
                    }
                    if (this.type === 'checkbox' || this.type === 'radio') {
                        this.checked = false;
                    } else {
                        $(this).val('');
                    }
                });

            tanggalBarangField.val(currentTanggal);
        });

        // Calculate total when jumlah or harga_satuan changes
        $('#jumlah, #harga_satuan').on('input', function() {
            calculateTotal();
        });
    });

    // Event handler untuk button view
    $(document).on('click', '.btn-view', function() {
        var id = $(this).data('id');
        viewLaporan(id);
    });

    // View laporan pemakaian barang
    function viewLaporan(id) {
        fetch(`/mekanik/laporan-pemakaian-barang/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#view_kode_unit').text(data.kode_unit);
                $('#view_nama_barang').text(data.nama_barang);
                $('#view_jumlah').text(data.jumlah);
                $('#view_bentuk_satuan').text(data.bentuk_satuan);
                $('#view_harga_satuan').text('Rp ' + formatNumber(data.harga_satuan));
                $('#view_total_harga').text('Rp ' + formatNumber(data.total_harga));
                $('#view_keterangan').text(data.keterangan || '-');
                $('#viewLaporanModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data laporan');
            });
    }
</script>

<!-- Modal View Laporan Pemakaian Barang -->
<div class="modal fade" id="viewLaporanModal" tabindex="-1" role="dialog" aria-labelledby="viewLaporanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewLaporanModalLabel">Detail Laporan Pemakaian Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Tanggal:</strong></label>
                            <p id="view_tanggal" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Kode Unit:</strong></label>
                            <p id="view_kode_unit" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Sparepart/Material/Jasa:</strong></label>
                    <p id="view_nama_barang" class="form-control-plaintext border p-2 rounded"></p>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Jumlah:</strong></label>
                            <p id="view_jumlah" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Bentuk Satuan:</strong></label>
                            <p id="view_bentuk_satuan" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Harga Satuan:</strong></label>
                            <p id="view_harga_satuan" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label><strong>Total Harga:</strong></label>
                            <p id="view_total_harga" class="form-control-plaintext border p-2 rounded"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Keterangan:</strong></label>
                    <p id="view_keterangan" class="form-control-plaintext border p-2 rounded"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection
