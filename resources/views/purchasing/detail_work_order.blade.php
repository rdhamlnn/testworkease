@extends('purchasing.master')

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Detail Work Order')

@section('styles')
<style>
    .detail-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .detail-header h4 {
        margin: 0;
        font-weight: 600;
    }
    
    .btn-action-group {
        display: flex;
        gap: 10px;
    }
    
    .form-group label {
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }
    
    .form-control-plaintext {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 4px;
        padding: 10px 15px;
        color: #495057;
    }
    
    .table-barang {
        margin-top: 20px;
    }
    
    .table-barang thead {
        background-color: #343a40;
        color: #fff;
    }
    
    .table-barang th {
        font-weight: 600;
        padding: 12px 15px;
        border: none;
    }
    
    .table-barang td {
        padding: 12px 15px;
        vertical-align: middle;
    }
    
    .table-barang tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    
    .total-row {
        background-color: #e9ecef !important;
        font-weight: 600;
    }
    
    .status-badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .dokumentasi-preview {
        max-width: 200px;
        max-height: 150px;
        object-fit: contain;
        border-radius: 8px;
        cursor: pointer;
        transition: transform 0.2s;
    }
    
    .dokumentasi-preview:hover {
        transform: scale(1.05);
    }
    
    .info-row {
        display: flex;
        gap: 20px;
        margin-bottom: 15px;
    }
    
    .info-col {
        flex: 1;
    }
    
    .section-title {
        font-size: 16px;
        font-weight: 600;
        color: #1B3C88;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #1B3C88;
    }
</style>
@endsection

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Detail Work Order</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('purchasing.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('purchasing.daftar-work-order') }}">Daftar Work Order</a></div>
            <div class="breadcrumb-item active">Detail Work Order</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card detail-card">
                    <div class="card-header">
                        <div class="detail-header w-100">
                            <h4>Detail Pengajuan Work Order</h4>
                            <div class="btn-action-group">
                                <a href="{{ route('purchasing.daftar-work-order') }}" class="btn btn-success">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                                @if($status == 'Menunggu')
                                    <form action="{{ route('purchasing.approve-work-order', $workOrder->id_surat_pengajuan) }}" method="POST" class="approve-form d-inline" 
                                        data-message="Yakin ingin menyetujui work order ini?"
                                        data-wo-id="{{ $workOrder->id_surat_pengajuan }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary approve-btn">
                                            <i class="fas fa-paper-plane mr-1"></i> Kirim
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Informasi Work Order -->
                        <div class="section-title">Informasi Work Order</div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No. Work Order</label>
                                    <p class="form-control-plaintext">{{ $workOrder->no_surat_pengajuan }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <p class="form-control-plaintext">{{ \Carbon\Carbon::parse($workOrder->tanggal)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Work Order</label>
                                    <p class="form-control-plaintext">{{ $workOrder->jenisWorkOrder->nama_jenis_wo ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <p class="form-control-plaintext">
                                        @if($status == 'Disetujui' || $status == 'Selesai')
                                            <span class="badge badge-success">{{ $status }}</span>
                                        @elseif(Str::contains($status, 'Ditolak'))
                                            <span class="badge badge-danger">{{ $status }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ $status }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Divisi Pengaju</label>
                                    <p class="form-control-plaintext">{{ $workOrder->divisi_pengaju }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ditujukan</label>
                                    <p class="form-control-plaintext">{{ $workOrder->ditujukan }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if(!$isPembelian)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unit/Code</label>
                                    <p class="form-control-plaintext">{{ $workOrder->unit ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Uraian</label>
                                    <p class="form-control-plaintext">{{ $workOrder->uraian }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($workOrder->dokumentasi && $workOrder->dokumentasi !== '-')
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Dokumentasi</label>
                                    <div>
                                        @php
                                            $fileExt = pathinfo($workOrder->dokumentasi, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                                        @endphp
                                        
                                        @if($isImage)
                                            <img src="{{ asset('storage/' . $workOrder->dokumentasi) }}" 
                                                 alt="Dokumentasi" 
                                                 class="dokumentasi-preview"
                                                 data-toggle="modal"
                                                 data-target="#modalViewDokumentasi">
                                        @else
                                            <a href="{{ asset('storage/' . $workOrder->dokumentasi) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-file mr-1"></i> Lihat Dokumentasi
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Daftar Barang (hanya untuk jenis Pembelian) -->
                        @if($isPembelian && count($barangItems) > 0)
                        <div class="section-title mt-4">Daftar Barang yang Diminta</div>
                        
                        <div class="table-responsive table-barang">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Barang</th>
                                        <th width="10%">Jumlah</th>
                                        <th width="15%">Satuan</th>
                                        <th width="20%">Estimasi Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($barangItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item['nama_barang'] }}</td>
                                        <td>{{ $item['jumlah'] }}</td>
                                        <td>{{ $item['satuan'] }}</td>
                                        <td>
                                            @if($item['estimasi_harga'] > 0)
                                                Rp {{ number_format($item['estimasi_harga'], 0, ',', '.') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="total-row">
                                        <td colspan="4" class="text-right"><strong>Total Harga:</strong></td>
                                        <td><strong>Rp {{ number_format($totalHarga, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @endif
                        
                        <!-- Tombol Aksi Bawah -->
                        @if($status == 'Menunggu')
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <form action="{{ route('purchasing.approve-work-order', $workOrder->id_surat_pengajuan) }}" method="POST" class="approve-form d-inline" 
                                data-message="Yakin ingin menyetujui work order ini?"
                                data-wo-id="{{ $workOrder->id_surat_pengajuan }}">
                                @csrf
                                <button type="submit" class="btn btn-success approve-btn">
                                    <i class="fas fa-check mr-1"></i> Setujui
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal View Dokumentasi -->
@if($workOrder->dokumentasi && $workOrder->dokumentasi !== '-')
<div class="modal fade" id="modalViewDokumentasi" tabindex="-1" role="dialog" aria-labelledby="modalViewDokumentasiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #1B3C88; color: #fff;">
                <h5 class="modal-title" id="modalViewDokumentasiLabel">Dokumentasi Work Order</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/' . $workOrder->dokumentasi) }}" alt="Dokumentasi" style="max-width: 100%; max-height: 500px; object-fit: contain; border-radius: 8px;">
                <p class="mt-3 mb-0">Dokumentasi Work Order: {{ $workOrder->no_surat_pengajuan }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Handle approve form
        $(document).on('submit', '.approve-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const message = form.data('message') || 'Yakin ingin menyetujui work order ini?';
            showApproveRejectConfirm(url, 'approve', message);
        });
    });
</script>
@endsection