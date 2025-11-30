@extends('atasan.master')

@section('title', 'Approval Permintaan')

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
        min-width: 200px !important;
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
        <h1>Daftar Permintaan Persetujuan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('atasan.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Daftar Permintaan</div>
        </div>
    </div>

    <div class="section-body">
        {{-- Notifikasi dihapus, menggunakan notifikasi fixed position di kanan atas dari components/notifications.blade.php --}}

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Permintaan yang Perlu Persetujuan</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="approvalPermintaanTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No. Permintaan</th>
                                        <th>No. Work Order</th>
                                        <th>Tanggal Permintaan</th>
                                        <th>Daftar Barang</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permintaanUntukApproval as $i => $pb)
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $pb->no_permintaan_barang }}</td>
                                            <td>{{ $pb->suratPengajuan->no_surat_pengajuan ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($pb->tanggal_permintaan)->locale('id')->isoFormat('DD MMMM YYYY') }}</td>
                                            <td>
                                                @php
                                                    $daftarBarang = $pb->daftarBarang ?? collect();
                                                @endphp
                                                @if($daftarBarang->count())
                                                    <ul class="mb-0">
                                                        @foreach($daftarBarang as $barang)
                                                            <li>{{ $barang->nama_barang ?? '-' }} ({{ $barang->jumlah ?? '-' }} {{ $barang->satuan ?? '-' }})</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($pb->total_estimasi_harga ?? 0, 0, ',', '') }}</td>
                                            <td>
                                                <span class="badge badge-warning">{{ $pb->status }}</span>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    {{-- Tombol Approve --}}
                                                    <form action="{{ route('atasan.approval-permintaan.approve', $pb->id_permintaan_barang) }}" method="POST" class="approve-form" style="display:inline;" 
                                                        data-message="Yakin ingin menyetujui permintaan ini?">
                                                        @csrf
                                                        @method('POST')
                                                        <button type="submit" class="btn btn-success btn-sm btn-icon" title="Setujui">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>

                                                    {{-- Tombol Reject pakai SweetAlert, bukan modal bootstrap --}}
                                                    <button type="button" 
                                                            class="btn btn-danger btn-sm btn-icon btn-reject" 
                                                            title="Tolak">
                                                        <i class="fas fa-times"></i>
                                                    </button>

                                                    {{-- Form reject tersembunyi yang akan disubmit via SweetAlert --}}
                                                    <form action="{{ route('atasan.approval-permintaan.reject', $pb->id_permintaan_barang) }}" method="POST" class="reject-form d-none">
                                                        @csrf
                                                        @method('POST')
                                                        <input type="hidden" name="catatan_atasan">
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Belum ada permintaan yang perlu approval</td>
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
@endsection

@section('scripts')
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        $('#approvalPermintaanTable').DataTable({
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

        // Konfirmasi approval menggunakan SweetAlert2
        $('.approve-form').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const message = $(form).data('message') || 'Yakin ingin menyetujui permintaan ini?';

            Swal.fire({
                title: 'Konfirmasi Approval',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        // Konfirmasi reject menggunakan SweetAlert2 dengan input catatan
        $('.btn-reject').on('click', function () {
            const $btn = $(this);
            const $form = $btn.closest('td').find('.reject-form');

            Swal.fire({
                title: 'Tolak Permintaan Barang',
                text: 'Masukkan alasan penolakan (opsional).',
                input: 'textarea',
                inputPlaceholder: 'Masukkan alasan penolakan',
                inputAttributes: {
                    'rows': 3
                },
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Tolak Permintaan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $form.find('input[name="catatan_atasan"]').val(result.value || '');
                    $form.submit();
                }
            });
        });

        setTimeout(() => {
            $('.alert').fadeOut();
        }, 3000);
    });
</script>
@endsection

