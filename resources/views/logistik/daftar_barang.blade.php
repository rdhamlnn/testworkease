@extends('logistik.master')

@section('title', 'Daftar Barang')

@section('styles')
<!-- Sweet Alert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        min-width: 150px !important;
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

    .preview-foto {
        max-width: 100px;
        max-height: 100px;
        object-fit: cover;
        border-radius: 4px;
        margin-top: 10px;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Daftar Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('logistik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Daftar Barang</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Barang</h4>
                        <div class="card-header-action">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalDaftarBarang" onclick="resetForm()">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="daftarBarangTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Barang</th>
                                        <th>Stok</th>
                                        <th>Satuan</th>
                                        <th>Harga Barang</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($daftarBarang as $db)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $db->nama_barang }}
                                            </td>
                                            <td>
                                                @php
                                                    $stok = $db->stok ?? 0;
                                                @endphp
                                                @if($stok == 0)
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-times-circle"></i> 0 (HABIS)
                                                    </span>
                                                @elseif($stok <= 10)
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-exclamation-triangle"></i> {{ number_format($stok, 0, ',', '.') }} (Rendah)
                                                    </span>
                                                @else
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check-circle"></i> {{ number_format($stok, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>{{ $db->satuan ?? '-' }}</td>
                                            <td>
                                                @if(!is_null($db->harga_barang))
                                                    Rp {{ number_format($db->harga_barang, 0, ',', '.') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <button type="button" class="btn btn-info btn-sm btn-icon btn-view" data-id="{{ $db->id_daftar_barang }}" title="Lihat Detail">
                                                        <img src="https://cdn-icons-png.flaticon.com/128/709/709612.png" alt="view">
                                                    </button>
                                                    <button type="button"
                                                        class="btn btn-warning btn-sm btn-icon btn-edit"
                                                        data-id="{{ $db->id_daftar_barang }}"
                                                        title="Edit">
                                                        <img src="https://cdn-icons-png.flaticon.com/128/2355/2355330.png" alt="edit">
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm btn-icon btn-delete"
                                                        data-url="{{ route('logistik.daftar-barang.destroy', $db->id_daftar_barang) }}"
                                                        data-message="Yakin ingin menghapus barang &quot;{{ $db->nama_barang }}&quot;?"
                                                        title="Hapus">
                                                        <img src="https://cdn-icons-png.flaticon.com/128/484/484611.png" alt="hapus">
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Belum ada data barang</td>
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

<!-- Modal Create/Edit Daftar Barang -->
<div class="modal fade" id="modalDaftarBarang" tabindex="-1" role="dialog" aria-labelledby="modalDaftarBarangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDaftarBarangLabel">Tambah Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formDaftarBarang" method="POST" action="{{ route('logistik.daftar-barang.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="formMethod" value="POST">
                <input type="hidden" name="id" id="formId">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nama_barang">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_barang" name="nama_barang" required placeholder="Masukkan nama barang">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="satuan">Satuan <span class="text-danger">*</span></label>
                                <select class="form-control" id="satuan" name="satuan" required>
                                    <option value="">Pilih Satuan</option>
                                    <option value="pcs">Pcs</option>
                                    <option value="kg">Kg</option>
                                    <option value="liter">Liter</option>
                                    <option value="botol">Botol</option>
                                    <option value="meter">Meter</option>
                                    <option value="roll">Roll</option>
                                    <option value="paket">Paket</option>
                                    <option value="karton">Karton</option>
                                    <option value="kantong">Kantong</option>
                                    <option value="kemasan">Kemasan</option>
                                    <option value="set">Set</option>
                                    <option value="tube">Tube</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stok">Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stok" name="stok" min="0" required placeholder="Masukkan jumlah stok">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="harga_barang">Harga Barang</label>
                        <input type="number" class="form-control" id="harga_barang" name="harga_barang" min="0" step="0.01" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="path_foto">Foto Barang</label>
                        <div id="currentFotoContainer" class="mb-2" style="display: none;">
                            <small class="text-muted d-block">File saat ini: <span id="currentFotoName" class="font-weight-bold"></span></small>
                        </div>
                        <input type="file" class="form-control" id="path_foto" name="path_foto" accept="image/*" onchange="previewFoto(this)">
                        <small class="form-text text-muted">Format: JPG, PNG. Maks. 2MB</small>
                        <div id="previewContainer" class="mt-2" style="display: none;">
                            <img id="previewFoto" class="preview-foto" src="" alt="Preview" style="max-width: 100%; max-height: 200px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd;">
                        </div>
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

<!-- Modal View Foto -->
<div class="modal fade" id="modalViewFoto" tabindex="-1" role="dialog" aria-labelledby="modalViewFotoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalViewFotoLabel">Foto Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="fotoBarangView" src="" alt="Foto Barang" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0" id="namaBarangView"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Barang -->
<div class="modal fade" id="modalDetailBarang" tabindex="-1" role="dialog" aria-labelledby="modalDetailBarangLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailBarangLabel">Detail Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Nama Barang</label>
                            <p id="detailNamaBarang" class="form-control border mb-0" style="background-color: #f8f9fa; padding: 8px 12px; min-height: 38px;">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Satuan</label>
                            <p id="detailSatuan" class="form-control border mb-0" style="background-color: #f8f9fa; padding: 8px 12px; min-height: 38px;">-</p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Stok</label>
                            <p id="detailStok" class="form-control border mb-0" style="background-color: #f8f9fa; padding: 8px 12px; min-height: 38px;">-</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold">Harga Barang</label>
                            <p id="detailHargaBarang" class="form-control border mb-0" style="background-color: #f8f9fa; padding: 8px 12px; min-height: 38px;">-</p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Foto Barang</label>
                    <div id="detailFotoContainer" class="form-control border mb-0" style="background-color: #f8f9fa; padding: 8px 12px; min-height: 38px; display: flex; align-items: center;">
                        <span class="text-muted">-</span>
                    </div>
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
<!-- Sweet Alert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let table;
    $(document).ready(function() {
        table = $('#daftarBarangTable').DataTable({
            "responsive": false,
            "scrollX": false,
            "autoWidth": false,
            "pageLength": 10,
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "paginate": {
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            }
        });

        setTimeout(() => {
            $('.alert').fadeOut();
        }, 3000);
    });

    // View Foto (dari tabel)
    $(document).on('click', '.btn-view-foto', function() {
        const fotoUrl = $(this).data('foto');
        const namaBarang = $(this).data('nama');
        
        $('#fotoBarangView').attr('src', fotoUrl);
        $('#fotoBarangView').attr('alt', namaBarang);
        $('#namaBarangView').text(namaBarang);
    });

    // View Foto (dari modal detail - tutup modal detail dulu, lalu buka modal foto)
    $(document).on('click', '.btn-view-foto-detail', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const fotoUrl = $(this).data('foto');
        const namaBarang = $(this).data('nama');
        
        // Set data foto
        $('#fotoBarangView').attr('src', fotoUrl);
        $('#fotoBarangView').attr('alt', namaBarang);
        $('#namaBarangView').text(namaBarang);
        
        // Tutup modal detail terlebih dahulu
        $('#modalDetailBarang').modal('hide');
        
        // Setelah modal detail tertutup, buka modal foto
        $('#modalDetailBarang').on('hidden.bs.modal', function() {
            $('#modalViewFoto').modal('show');
            // Hapus event listener setelah digunakan
            $('#modalDetailBarang').off('hidden.bs.modal');
        });
    });

    // Reset form dan modal
    function resetForm() {
        $('#formDaftarBarang')[0].reset();
        $('#formMethod').val('POST');
        $('#formId').val('');
        $('#modalDaftarBarangLabel').text('Tambah Barang');
        $('#previewContainer').hide();
        $('#previewFoto').attr('src', '');
        $('#currentFotoContainer').hide();
        $('#currentFotoName').text('');
        $('#formDaftarBarang').attr('action', '{{ route("logistik.daftar-barang.store") }}');
    }

    // Preview foto
    function previewFoto(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            // Sembunyikan file saat ini jika ada
            $('#currentFotoContainer').hide();
            
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewFoto').attr('src', e.target.result);
                $('#previewContainer').show();
            };
            reader.readAsDataURL(file);
        } else {
            // Jika file dihapus, tampilkan kembali file saat ini jika ada
            const currentFile = $('#currentFotoName').text();
            if (currentFile) {
                $('#currentFotoContainer').show();
            }
            $('#previewContainer').hide();
        }
    }

    // View/Detail barang
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '{{ url("logistik/daftar-barang") }}/' + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#detailNamaBarang').text(data.nama_barang || '-');
                    $('#detailSatuan').text(data.satuan || '-');
                    // Update stok dengan indikator visual
                    var stok = data.stok || 0;
                    var stokHtml = '';
                    if (stok == 0) {
                        stokHtml = '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> 0 (HABIS)</span>';
                    } else if (stok <= 10) {
                        stokHtml = '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> ' + stok.toLocaleString('id-ID') + ' (Rendah)</span>';
                    } else {
                        stokHtml = '<span class="badge badge-success"><i class="fas fa-check-circle"></i> ' + stok.toLocaleString('id-ID') + '</span>';
                    }
                    $('#detailStok').html(stokHtml);
                    
                    if (data.harga_barang) {
                        $('#detailHargaBarang').text('Rp ' + new Intl.NumberFormat('id-ID').format(data.harga_barang));
                    } else {
                        $('#detailHargaBarang').html('<span class="text-muted">-</span>');
                    }
                    
                    if (data.path_foto) {
                        $('#detailFotoContainer').html(
                            '<button type="button" class="btn btn-sm btn-outline-primary btn-view-foto-detail" ' +
                            'data-foto="{{ asset("storage") }}/' + data.path_foto + '" ' +
                            'data-nama="' + data.nama_barang + '">' +
                            '<i class="fas fa-image"></i> Lihat Foto' +
                            '</button>'
                        );
                    } else {
                        $('#detailFotoContainer').html('<span class="text-muted">-</span>');
                    }
                    
                    $('#modalDetailBarang').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal memuat data barang',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    });

    // Edit barang
    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: '{{ url("logistik/daftar-barang") }}/' + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    $('#formId').val(data.id_daftar_barang);
                    $('#nama_barang').val(data.nama_barang);
                    $('#satuan').val(data.satuan);
                    $('#stok').val(data.stok);
                    $('#harga_barang').val(data.harga_barang);
                    $('#formMethod').val('PUT');
                    $('#modalDaftarBarangLabel').text('Edit Barang');
                    // Gunakan route dengan parameter yang benar
                    const updateUrl = '{{ route("logistik.daftar-barang.update", ":id") }}'.replace(':id', id);
                    $('#formDaftarBarang').attr('action', updateUrl);
                    
                    // Preview foto lama jika ada
                    if (data.path_foto) {
                        const fileName = data.path_foto.split('/').pop();
                        $('#currentFotoName').text(fileName);
                        $('#currentFotoContainer').show();
                        $('#previewFoto').attr('src', '{{ asset("storage") }}/' + data.path_foto);
                        $('#previewContainer').show();
                    } else {
                        $('#currentFotoContainer').hide();
                        $('#previewContainer').hide();
                    }
                    
                    // Buka modal
                    $('#modalDaftarBarang').modal('show');
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal memuat data barang',
                    confirmButtonColor: '#3085d6'
                });
            }
        });
    });

    // Submit form
    $('#formDaftarBarang').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const url = $(this).attr('action');
        const method = $('#formMethod').val();
        
        // Route sudah menggunakan POST untuk create dan update
        // Tidak perlu method spoofing karena route sudah POST
        // Pastikan CSRF token ada
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            formData.set('_token', csrfToken);
        }
        
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': csrfToken || $('input[name="_token"]').first().val(),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $('#modalDaftarBarang').modal('hide');
                    // Redirect menggunakan URL dari response atau current URL dengan from=crud
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.set('from', 'crud');
                        window.location.href = currentUrl.toString();
                    }
                } else {
                    // Tampilkan error toast
                    if (typeof triggerToast === 'function') {
                        triggerToast(response.message || 'Terjadi kesalahan', 'danger');
                    } else {
                        alert(response.message || 'Terjadi kesalahan');
                    }
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'Gagal menyimpan data';
                
                if (response && response.message) {
                    errorMessage = response.message;
                } else if (xhr.status === 405) {
                    errorMessage = 'Method tidak didukung. Silakan refresh halaman dan coba lagi.';
                } else if (xhr.status === 422) {
                    errorMessage = 'Data tidak valid. Silakan periksa kembali.';
                }
                
                // Tampilkan error toast
                if (typeof triggerToast === 'function') {
                    triggerToast(errorMessage, 'danger');
                } else {
                    alert(errorMessage);
                }
            }
        });
    });

</script>
@include('components.delete-confirm-modal')
@endsection
