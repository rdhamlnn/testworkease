@extends('purchasing.master')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Daftar Pengajuan Work Order')

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
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Daftar Pengajuan Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('purchasing.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Daftar Pengajuan Work Order</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Daftar Pengajuan Work Order</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="daftarPengajuanTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>No Work-Order</th>
                                        <th>Jenis WO</th>
                                        <th>Divisi Pengaju</th>
                                        <th>Hari/Tanggal</th>
                                        <th>Unit/Code</th>
                                        <th>Barang</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Total Harga</th>
                                        <th>Uraian</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($workOrders as $i => $wo)
                                        @php
                                            $status = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';
                                            $jenisWo = $wo->jenisWorkOrder ? strtolower($wo->jenisWorkOrder->nama_jenis_wo) : '';
                                            $isPembelian = $jenisWo === 'pembelian';
                                            $isPermintaan = $jenisWo === 'permintaan';
                                            $isPerbaikan = $jenisWo === 'perbaikan';
                                            
                                            $barangItems = [];
                                            $qtyItems = [];
                                            $satuanItems = [];
                                            
                                            $barangLookup = isset($daftarBarang) ? collect($daftarBarang)->keyBy('nama_barang') : collect();
                                            
                                            if ($isPembelian && $wo->unit && $wo->unit !== '-') {
                                                $parts = explode(', ', $wo->unit);
                                                foreach ($parts as $part) {
                                                    if (preg_match('/^(.+?)\s*\(qty:\s*(\d+)\)$/i', trim($part), $matches)) {
                                                        $namaBarang = trim($matches[1]);
                                                        $barangItems[] = $namaBarang;
                                                        $qtyItems[] = (int)$matches[2];
                                                        $satuanItems[] = $barangLookup->get($namaBarang)->satuan ?? '-';
                                                    } elseif (!empty(trim($part))) {
                                                        $namaBarang = trim($part);
                                                        $barangItems[] = $namaBarang;
                                                        $qtyItems[] = 1;
                                                        $satuanItems[] = $barangLookup->get($namaBarang)->satuan ?? '-';
                                                    }
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
                                            <td>{{ $wo->no_surat_pengajuan }}</td>
                                            <td>
                                                @if($wo->jenisWorkOrder)
                                                    {{ $wo->jenisWorkOrder->nama_jenis_wo }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $wo->divisi_pengaju }}</td>
                                            <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
                                            
                                            <td>
                                                @if($isPembelian)
                                                    <span class="text-muted">-</span>
                                                @elseif($isPermintaan)
                                                    <span class="text-muted">-</span>
                                                @elseif($isPerbaikan)
                                                    {{ $wo->unit && $wo->unit !== '-' ? $wo->unit : '-' }}
                                                @else
                                                    {{ $wo->unit ?? '-' }}
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($isPembelian && count($barangItems) > 0)
                                                    {{ implode(', ', $barangItems) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($isPembelian && count($qtyItems) > 0)
                                                    {{ implode(', ', $qtyItems) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            
                                            <td>
                                                @if($isPembelian && count($satuanItems) > 0)
                                                    {{ implode(', ', $satuanItems) }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            
                                            {{-- Kolom Total Harga --}}
                                            <td>Rp {{ number_format($wo->calculated_total_harga ?? 0, 0, ',', '.') }}</td>
                                            
                                            <td>{{ Str::limit($wo->uraian, 30) }}</td>
                                            <td>
                                                @if($status == 'Disetujui' || $status == 'Selesai')
                                                    <span class="badge badge-success">{{ $status }}</span>
                                                @elseif(Str::contains($status, 'Ditolak'))
                                                    <span class="badge badge-danger">{{ $status }}</span>
                                                @else
                                                    <span class="badge badge-warning">{{ $status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('purchasing.work-order.detail', $wo->id_surat_pengajuan) }}" 
                                                        class="btn btn-info btn-sm btn-view" 
                                                        title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($status == 'Menunggu')
                                                        <form action="{{ route('purchasing.reject-work-order', $wo->id_surat_pengajuan) }}" method="POST" class="reject-form" style="display:inline;" 
                                                            data-message="Yakin ingin menolak work order ini?"
                                                            data-wo-id="{{ $wo->id_surat_pengajuan }}">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="btn btn-danger btn-sm btn-icon reject-btn" 
                                                                    data-wo-id="{{ $wo->id_surat_pengajuan }}"
                                                                    title="Tolak">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="13" class="text-center text-muted">Belum ada data work order</td>
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
<script>
    $(document).ready(function() {
        $('#daftarPengajuanTable').DataTable({
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
                "emptyTable": "Tidak ada data pengajuan work order"
            }
        });

        // Handler untuk tombol Tolak
        $(document).on('submit', '.reject-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const message = form.data('message') || 'Yakin ingin menolak work order ini?';
            showApproveRejectConfirm(url, 'reject', message);
        });
    });
</script>
@endsection

