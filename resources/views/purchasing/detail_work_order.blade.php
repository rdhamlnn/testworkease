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
    
    /* Select2 Bootstrap 4 Theme Fixes */
    .select2-container--bootstrap4 .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px) !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
        line-height: calc(1.5em + 0.75rem) !important;
        color: #6c757d;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css">
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
                                    <p>{{ $workOrder->no_surat_pengajuan }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tanggal</label>
                                    <p>{{ \Carbon\Carbon::parse($workOrder->tanggal)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Jenis Work Order</label>
                                    <p>{{ $workOrder->jenisWorkOrder->nama_jenis_wo ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <p>
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
                                    <p>{{ $workOrder->divisi_pengaju }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ditujukan</label>
                                    <p>{{ $workOrder->ditujukan }}</p>
                                </div>
                            </div>
                        </div>
                        
                        @if(!$isPembelian)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Unit/Code</label>
                                    <p>{{ $workOrder->unit ?? '-' }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Uraian</label>
                                    <p>{{ $workOrder->uraian }}</p>
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
                        <div class="section-title mt-4">Daftar Barang Realisasi</div>           
                             <div class="table-responsive table-barang">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nama Barang</th>
                                        <th width="10%">Jumlah</th>
                                        <th width="20%">Harga Satuan</th>
                                        <th width="20%">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($realisasiItems as $index => $item)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $item->barang->nama_barang ?? 'Unknown' }}</td>
                                        <td>{{ $item->jumlah }} {{ $item->barang->satuan ?? '' }}</td>
                                        <td>Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                        <td>Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                    </tr>
                                    @endforeach
                                    @if(count($realisasiItems) == 0)
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Belum ada data realisasi pembelian.</td>
                                    </tr>
                                    @endif
                                    <tr class="total-row">
                                        <td colspan="4" class="text-right"><strong>Total Realisasi:</strong></td>
                                        <td><strong>Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @endif

                        <!-- Form Input Harga Pembelian (Log) -->
                        @if($isPembelian && count($barangItems) > 0)
                        <div class="section-title mt-4">Input Harga Pembelian (Log)</div>
                        <div class="card bg-light border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <form action="{{ route('purchasing.store-harga-barang') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="id_surat_pengajuan" value="{{ $workOrder->id_surat_pengajuan }}">
                                    
                                    <div class="row align-items-end">
                                        <div class="col-md-5">
                                            <div class="form-group mb-0">
                                                <label for="id_barang">Pilih Barang yang Dibeli</label>
                                                <select class="form-control select2" id="id_barang" name="id_barang" required style="width: 100%;">
                                                    <option value="">-- Pilih Barang --</option>
                                                    @foreach($barangItems as $item)
                                                        @if(isset($item['id_barang']) && $item['id_barang'])
                                                            <option value="{{ $item['id_barang'] }}" data-harga="{{ $item['harga_satuan'] }}">
                                                                {{ $item['nama_barang'] }} (Qty: {{ $item['jumlah'] }} {{ $item['satuan'] }})
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <small class="form-text text-muted">Hanya barang yang terdaftar di master data yang bisa dipilih.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group mb-0">
                                                <label for="harga_barang">Harga Beli Satuan (Rp)</label>
                                                <input type="number" class="form-control" id="harga_barang" name="harga_barang" min="0" required placeholder="0">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button type="submit" class="btn btn-primary btn-block">
                                                <i class="fas fa-save mr-1"></i> Update Harga
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Init Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Auto-fill harga saat barang dipilih
        $('#id_barang').on('change', function() {
            var selectedOption = $(this).find('option:selected');
            var harga = selectedOption.data('harga');
            // Jika harga ada dan > 0, isi field harga
            if (harga && harga > 0) {
                $('#harga_barang').val(harga);
            } else {
                $('#harga_barang').val('');
            }
        });

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