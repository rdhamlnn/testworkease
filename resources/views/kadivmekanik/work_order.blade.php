@extends('kadivmekanik.master')

@section('title', 'Work Order')

@section('styles')
<style>
    .table th,
    .table td {
        text-align: left !important;
        vertical-align: middle;
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

    .btn-action {
        margin-right: 5px;
    }

    .btn-action:last-child {
        margin-right: 0;
    }

    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .priority-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    .priority-high {
        background-color: #dc3545;
        color: white;
    }

    .priority-medium {
        background-color: #ffc107;
        color: #212529;
    }

    .priority-low {
        background-color: #28a745;
        color: white;
    }

    .status-pending {
        background-color: #ffc107;
        color: #212529;
    }

    .status-approved {
        background-color: #28a745;
        color: white;
    }

    .status-rejected {
        background-color: #dc3545;
        color: white;
    }

    .status-in-progress {
        background-color: #17a2b8;
        color: white;
    }

    .status-completed {
        background-color: #6c757d;
        color: white;
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
    
    /* Fix DataTable dropdown border bug */
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
        width: auto !important;
        min-width: 80px !important;
    }
    
    .dataTables_length select:focus {
        border-color: #86b7fe !important;
        outline: 0 !important;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
    }

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
        min-width: 180px !important;
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
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('kadivmekanik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Work Order</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <div class="row">
            <div class="col-12">
                <!-- Work Order -->
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Work Order</h4>
                        <div class="card-header-action">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#tambahWorkOrderModal">
                                <i class="fas fa-plus"></i> Tambah Work Order
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="workOrderTable">
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
                                @php
                                    $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                                    $statusLabel = $status ?? 'Menunggu';
                                    $statusLower = strtolower($statusLabel);
                                    $isLocked = in_array($wo->id_verifikator, [2, 3]);
                                @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $wo->no_work_order }}</td>
                                    <td>
                                        @if($wo->jenisWorkOrder)
                                            {{ $wo->jenisWorkOrder->nama_jenis_wo }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $wo->divisi_pengaju }}</td>
                                    <td>{{ $wo->ditujukan }}</td>
                                    <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                    <td>{{ $wo->unit_code }}</td>
                                    <td>{{ Str::limit($wo->uraian, 30) }}</td>
                                    <td>
                                        @if($status == 'Disetujui' || $status == 'Selesai')
                                            <span class="badge badge-success">{{ $status }}</span>
                                        @elseif($status == 'Ditolak')
                                            <span class="badge badge-danger">{{ $status }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ $status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" class="btn btn-info btn-sm btn-icon btn-view" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" title="Lihat Detail">
                                                <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" alt="view">
                                            </button>
                                            @if($status == 'Menunggu')
                                            <button type="button" class="btn btn-warning btn-sm btn-icon btn-edit" 
                                                data-id="{{ $wo->id_surat_pengajuan }}" title="Edit"
                                                data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                data-lock-message="{{ $isLocked ? 'Work Order tidak dapat diedit karena status sudah '.$statusLower.'.' : '' }}">
                                                <img src="https://cdn-icons-png.flaticon.com/128/2355/2355330.png" alt="edit">
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm btn-icon btn-delete" 
                                                data-url="{{ route('kadivmekanik.hapus-work-order', $wo->id_surat_pengajuan) }}"
                                                data-message="Yakin ingin menghapus work order ini?" title="Hapus"
                                                data-locked="{{ $isLocked ? 'true' : 'false' }}"
                                                data-lock-message="{{ $isLocked ? 'Work Order tidak dapat dihapus karena status sudah '.$statusLower.'.' : '' }}">
                                                <img src="https://cdn-icons-png.flaticon.com/128/484/484611.png" alt="hapus">
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

<!-- Modal Tambah Work Order -->
<div class="modal fade" id="tambahWorkOrderModal" tabindex="-1" role="dialog" aria-labelledby="tambahWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahWorkOrderModalLabel">Tambah Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="tambahWorkOrderForm" action="{{ route('kadivmekanik.work-order.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="no_work_order">No. Work Order</label>
                                <input type="text" class="form-control" id="no_work_order" name="no_work_order" readonly value="{{ $nextWorkOrderNumber ?? '01/MKN/KCE/2025' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tanggal">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="divisi_pengaju">Divisi Pengaju <span class="text-danger">*</span></label>
                                @php
                                    $userDivisiId = session('user_divisi');
                                    $divisiPengaju = \Illuminate\Support\Facades\DB::table('divisi')->where('id_divisi', $userDivisiId)->value('nama_divisi') ?? 'Mekanik';
                                @endphp
                                <input type="text" class="form-control" id="divisi_pengaju" value="{{ $divisiPengaju }}" readonly>
                                <input type="hidden" name="divisi_pengaju" value="{{ $divisiPengaju }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                <select class="form-control" name="ditujukan" required>
                                    <option value="">-- Pilih Divisi --</option>
                                    @foreach($divisi as $d)
                                        @if($d->nama_divisi !== 'Administrator' && $d->nama_divisi !== $divisiPengaju)
                                            <option value="{{ $d->nama_divisi }}">{{ $d->nama_divisi }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="id_jenis_wo">Jenis Work Order <span class="text-danger">*</span></label>
                                <select class="form-control" name="id_jenis_wo" id="id_jenis_wo" required>
                                    <option value="">-- Pilih Jenis Work Order --</option>
                                    @foreach($jenisWorkOrder as $jenis)
                                        <option value="{{ $jenis->id_jenis_wo }}">{{ $jenis->nama_jenis_wo }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="unit">Nama Unit / Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="unit" name="unit" required placeholder="Masukkan unit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="dokumentasi">Dokumentasi</label>
                                <input type="file" class="form-control" id="dokumentasi" name="dokumentasi" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Format: JPG, PNG, PDF. Maksimal 2MB.</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="uraian">Uraian <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="uraian" name="uraian" rows="3" required placeholder="Masukkan uraian work order"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="reset" class="btn btn-warning">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Work Order -->
<div class="modal fade" id="editWorkOrderModal" tabindex="-1" role="dialog" aria-labelledby="editWorkOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editWorkOrderModalLabel">Edit Work Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editWorkOrderForm" action="" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_no_work_order">No. Work Order</label>
                                <input type="text" class="form-control" id="edit_no_work_order" name="no_work_order" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_tanggal">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_divisi_pengaju">Divisi Pengaju</label>
                                <input type="text" class="form-control" id="edit_divisi_pengaju" name="divisi_pengaju" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_ditujukan" name="ditujukan" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_id_jenis_wo">Jenis Work Order <span class="text-danger">*</span></label>
                                <select class="form-control" name="id_jenis_wo" id="edit_id_jenis_wo" required>
                                    <option value="">-- Pilih Jenis Work Order --</option>
                                    @foreach($jenisWorkOrder as $jenis)
                                        <option value="{{ $jenis->id_jenis_wo }}">{{ $jenis->nama_jenis_wo }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_unit">Nama Unit / Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_unit" name="unit" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_dokumentasi">Dokumentasi</label>
                                <div id="currentDokumentasiKadiv" class="mb-2" style="display: none;">
                                    <small class="text-muted d-block">File saat ini: <span id="currentDokumentasiNameKadiv" class="font-weight-bold"></span></small>
                                </div>
                                <input type="file" class="form-control" id="edit_dokumentasi" name="dokumentasi" accept=".pdf,.jpg,.jpeg,.png" onchange="previewDokumentasiEditKadiv(this)">
                                <small class="form-text text-muted">Format: JPG, PNG, PDF. Maks. 2MB</small>
                                <div id="previewDokumentasiContainerKadiv" class="mt-2" style="display: none;">
                                    <img id="previewDokumentasiKadiv" src="" alt="Preview Dokumentasi" style="max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_uraian">Uraian <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit_uraian" name="uraian" rows="3" required></textarea>
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
                            <label><strong>Unit:</strong></label>
                            <p id="view_unit" class="form-control-plaintext border p-2 rounded"></p>
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
    // Tutup otomatis alert setelah 3 detik
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 3000);

    function isLockedAction(element) {
        const raw = $(element).data('locked');
        return raw === true || raw === 'true';
    }

    function notifyLockedAction(element, fallbackMessage) {
        const message = $(element).data('lockMessage') || fallbackMessage || 'Work Order tidak dapat diproses karena status saat ini.';
        if (typeof window.triggerToast === 'function') {
            window.triggerToast(message, 'danger', 5200);
        } else {
            alert(message);
        }
    }

    // Initialize DataTable
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

        // Set tanggal hari ini untuk form tambah
        var today = new Date().toISOString().split('T')[0];
        $('#tanggal').val(today);

        // Event handler untuk button view
        $(document).on('click', '.btn-view', function() {
            var id = $(this).data('id');
            viewWorkOrder(id);
        });

        // Event handler untuk button edit
        $(document).on('click', '.btn-edit', function(e) {
            if (isLockedAction(this)) {
                e.preventDefault();
                e.stopPropagation();
                notifyLockedAction(this, 'Work Order tidak dapat diedit karena status sudah final.');
                return;
            }

            var id = $(this).data('id');
            editWorkOrder(id);
        });
    });

    // Form tambah work order - biarkan submit normal ke server
    $('#tambahWorkOrderForm').on('submit', function() {
        // Form akan submit ke server secara normal
        // Server akan handle validasi dan redirect
    });

    // Form edit work order - biarkan submit normal ke server
    $('#editWorkOrderForm').on('submit', function() {
        // Form akan submit ke server secara normal
        // Server akan handle validasi dan redirect
    });

    // View work order
    function viewWorkOrder(id) {
        // Ambil data dari server
        fetch(`/kadivmekanik/work-order/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#view_no_wo').text(data.no_work_order);
                $('#view_tanggal').text(new Date(data.tanggal).toLocaleDateString('id-ID'));
                $('#view_divisi_pengaju').text(data.divisi_pengaju);
                $('#view_ditujukan').text(data.ditujukan);
                // Fix unit display - show unit name if available, otherwise show unit string
                let unitDisplay = '-';
                if (data.unit && typeof data.unit === 'string') {
                    unitDisplay = data.unit;
                } else if (data.unit && data.unit.nama_unit) {
                    unitDisplay = data.unit.nama_unit;
                } else if (data.unit_code) {
                    unitDisplay = data.unit_code;
                }
                $('#view_unit').text(unitDisplay);
                
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

    // Edit work order
    function editWorkOrder(id) {
        // Ambil data dari server
        fetch(`/kadivmekanik/work-order/${id}`)
            .then(response => response.json())
            .then(data => {
                $('#edit_id').val(data.id_surat_pengajuan);
                $('#edit_no_work_order').val(data.no_work_order);
                $('#edit_tanggal').val(data.tanggal);
                $('#edit_divisi_pengaju').val(data.divisi_pengaju);
                $('#edit_ditujukan').val(data.ditujukan);
                $('#edit_id_jenis_wo').val(data.id_jenis_wo);
                $('#edit_unit').val(data.unit);
                $('#edit_uraian').val(data.uraian);
                
                // Handle dokumentasi preview
                if (data.dokumentasi && data.dokumentasi !== '-') {
                    const fileExt = data.dokumentasi.split('.').pop().toLowerCase();
                    const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(fileExt);
                    const fileName = data.dokumentasi.split('/').pop();
                    
                    // Tampilkan nama file saat ini
                    $('#currentDokumentasiNameKadiv').text(fileName);
                    $('#currentDokumentasiKadiv').show();
                    
                    // Jika gambar, tampilkan preview
                    if (isImage) {
                        $('#previewDokumentasiKadiv').attr('src', '/storage/' + data.dokumentasi);
                        $('#previewDokumentasiContainerKadiv').show();
                    } else {
                        $('#previewDokumentasiContainerKadiv').hide();
                    }
                } else {
                    $('#currentDokumentasiKadiv').hide();
                    $('#previewDokumentasiContainerKadiv').hide();
                }
                
                // Set form action
                $('#editWorkOrderForm').attr('action', `/kadivmekanik/work-order/${data.id_surat_pengajuan}`);
                
                $('#editWorkOrderModal').modal('show');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal mengambil data work order');
            });
    }

    // Delete work order - menggunakan modal konfirmasi
    function deleteWorkOrder(id) {
        const url = `/kadivmekanik/work-order/${id}`;
        showDeleteConfirm(url, 'Apakah Anda yakin ingin menghapus work order ini?');
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
@include('components.delete-confirm-modal')

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