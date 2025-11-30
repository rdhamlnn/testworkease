@extends('admin.master')

@section('title', 'Kelola Divisi')

@section('styles')
<style>
    /* Tabel */
    .table th,
    .table td {
        text-align: left !important;
        vertical-align: middle;
    }

    /* Modal Styling */
    .modal-header {
        background-color: #1B3C88;
        color: #fff;
    }

    .modal-header .close {
        color: #fff;
        opacity: 1;
    }

    .modal-header .close:hover {
        opacity: 0.8;
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

    /* Container table-responsive untuk scroll horizontal */
    .table-responsive {
        display: block !important;
        width: 100%;
        overflow-x: visible !important;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100% !important;
        min-width: 600px !important;
        table-layout: auto;
        border-collapse: collapse !important;
    }

    .table th:last-child,
    .table td:last-child {
        white-space: nowrap !important;
        min-width: 120px !important;
        padding: 10px 8px !important;
    }

    .action-buttons {
        display: flex !important;
        align-items: center;
        justify-content: flex-start;
        gap: 5px;
        flex-wrap: nowrap !important;
        min-height: 32px;
        white-space: nowrap !important;
    }

    .action-buttons .btn {
        margin: 0;
        padding: 4px 8px;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        flex-shrink: 0 !important;
        white-space: nowrap !important;
    }

    .action-buttons .btn-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .card-body {
        overflow-x: auto !important;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    .card-body > .table-responsive {
        min-width: 600px !important;
    }

    .dataTables_wrapper {
        width: 100% !important;
        min-width: 600px !important;
        overflow-x: visible;
        display: block !important;
    }

    .dataTables_wrapper > .row:first-child,
    .dataTables_wrapper > .row:last-child {
        min-width: 600px !important;
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
        <h1>Kelola Divisi</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="#">Data Master</a></div>
            <div class="breadcrumb-item active">Kelola Divisi</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        {{-- Card Daftar Divisi --}}
        <div class="card">
            <div class="card-header">
                <h4>Daftar Divisi</h4>
                <div class="card-header-action">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambahDivisi">
                        <i class="fas fa-plus"></i> Tambah Divisi
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="divisiTable">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Divisi</th>
                                <th style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($divisi as $index => $d)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $d->nama_divisi }}</td>
                                    <td>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" class="btn btn-warning btn-sm btn-icon btn-edit"
                                                data-id="{{ $d->id_divisi }}"
                                                data-toggle="modal" data-target="#modalEditDivisi" title="Edit">
                                                <img src="https://cdn-icons-png.flaticon.com/128/2355/2355330.png"
                                                    alt="edit" style="width:14px; filter: brightness(0) invert(1);">
                                            </button>

                                            <button type="button" class="btn btn-danger btn-sm btn-icon btn-delete" 
                                                data-url="{{ route('admin.hapus-divisi', $d->id_divisi) }}"
                                                data-message="Yakin ingin menghapus divisi ini?" title="Hapus">
                                                <img src="https://cdn-icons-png.flaticon.com/128/484/484611.png" alt="hapus" style="width:14px; filter: brightness(0) invert(1);">
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada data divisi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Tambah Divisi -->
<div class="modal fade" id="modalTambahDivisi" tabindex="-1" role="dialog" aria-labelledby="modalTambahDivisiLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDivisiLabel">Tambah Divisi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.simpan-divisi') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Divisi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_divisi" class="form-control" required placeholder="Masukkan nama divisi">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="reset" class="btn btn-warning">Reset</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Divisi -->
<div class="modal fade" id="modalEditDivisi" tabindex="-1" role="dialog" aria-labelledby="modalEditDivisiLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditDivisiLabel">Edit Divisi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="formEditDivisi" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Divisi <span class="text-danger">*</span></label>
                        <input type="text" name="nama_divisi" id="edit_nama_divisi" class="form-control" required placeholder="Masukkan nama divisi">
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
@endsection

@section('scripts')
<script>
    // Initialize DataTable
    $(document).ready(function() {
        $('#divisiTable').DataTable({
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
                "emptyTable": "Tidak ada data divisi"
            }
        });
    });

    // Tutup otomatis alert setelah 3 detik
    setTimeout(() => {
        $('.alert').alert('close');
    }, 3000);

    // Handle tombol Edit - load data via AJAX
    $('.btn-edit').on('click', function() {
        var id = $(this).data('id');
        var url = '{{ url("/admin/divisi/get") }}/' + id;
        
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                $('#edit_nama_divisi').val(data.nama_divisi);
                
                // Set action form
                $('#formEditDivisi').attr('action', '{{ url("/admin/divisi/update") }}/' + id);
            },
            error: function() {
                alert('Gagal mengambil data divisi');
            }
        });
    });
</script>
@include('components.delete-confirm-modal')
@endsection
