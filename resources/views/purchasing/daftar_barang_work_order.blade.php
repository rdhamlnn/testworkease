@extends('purchasing.master')

@section('title', 'Daftar Barang Work Order')

@section('styles')
<!-- Sweet Alert CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    /* Styling standar tabel responsif */
    .table-responsive {
        display: block !important;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Daftar Barang Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('purchasing.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active">Daftar Barang WO</div>
        </div>
    </div>

    <div class="section-body">
        <p class="section-lead">Riwayat aktivitas harga barang yang dibeli berdasarkan Work Order.</p>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Riwayat Pembelian</h4>
                        <!-- Tombol Tambah dihapus sesuai instruksi -->
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="logPembelianTable">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Tanggal Input</th>
                                        <th>No. Work Order</th>
                                        <th>Nama Barang</th>
                                        <th>Qty</th>
                                        <th>Satuan</th>
                                        <th>Harga Satuan</th>
                                        <th>Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pembelian as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($item->workOrder)
                                                    <a href="{{ route('purchasing.work-order.detail', $item->id_surat_pengajuan) }}">
                                                        {{ $item->workOrder->no_surat_pengajuan }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->barang ? $item->barang->nama_barang : '-' }}
                                            </td>
                                            <td>
                                                {{ number_format($item->jumlah) }}
                                            </td>
                                            <td>
                                                {{ $item->barang ? $item->barang->satuan : '-' }}
                                            </td>
                                            <td>
                                                Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}
                                            </td>
                                            <td>
                                                Rp {{ number_format($item->total_harga, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">Belum ada data pembelian tercatat.</td>
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
<!-- Sweet Alert JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#logPembelianTable').DataTable({
            "language": {
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                "emptyTable": "Belum ada data pembelian tercatat",
                "zeroRecords": "Tidak ditemukan data yang cocok",
                "paginate": {
                    "next": "Selanjutnya",
                    "previous": "Sebelumnya"
                }
            },
            "order": [[ 1, "desc" ]] // Urutkan berdasarkan tanggal (kolom index 1) secara descending
        });
    });
</script>
@endsection
