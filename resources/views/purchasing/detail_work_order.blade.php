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
                        {{-- Sembunyikan form input pembelian jika status sudah 'Disetujui' atau 'Selesai' --}}
                        @if($isPembelian && count($barangItems) > 0 && !in_array($status, ['Disetujui', 'Selesai']))
                        <div class="section-title mt-4">Input Barang yang Akan Dibeli</div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-1"></i> 
                            Pilih barang yang akan dibeli dari daftar work order. Barang yang tidak dipilih tidak akan masuk ke daftar pembelian.
                        </div>
                        <div class="card bg-light border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <form action="{{ route('purchasing.store-harga-barang') }}" method="POST" id="formPembelianBarang">
                                    @csrf
                                    <input type="hidden" name="id_surat_pengajuan" value="{{ $workOrder->id_surat_pengajuan }}">
                                    
                                    <!-- Table untuk input barang yang akan dibeli -->
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered" id="tablePembelian">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th width="5%"><input type="checkbox" id="checkAll" title="Pilih Semua"></th>
                                                    <th>Nama Barang</th>
                                                    <th width="10%">Qty</th>
                                                    <th width="10%">Satuan</th>
                                                    <th width="20%">Harga Beli Satuan (Rp)</th>
                                                    <th width="20%">Total Harga</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($barangItems as $index => $item)
                                                    @if(isset($item['id_barang']) && $item['id_barang'])
                                                    <tr>
                                                        <td class="text-center">
                                                            <input type="checkbox" 
                                                                   name="barang_dipilih[]" 
                                                                   value="{{ $item['id_barang'] }}" 
                                                                   class="barang-checkbox"
                                                                   data-index="{{ $index }}">
                                                        </td>
                                                        <td>
                                                            {{ $item['nama_barang'] }}
                                                            <input type="hidden" name="barang[{{ $item['id_barang'] }}][nama]" value="{{ $item['nama_barang'] }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control form-control-sm qty-input" 
                                                                   name="barang[{{ $item['id_barang'] }}][jumlah]" 
                                                                   value="{{ $item['jumlah'] }}" 
                                                                   min="1"
                                                                   data-original-qty="{{ $item['jumlah'] }}"
                                                                   disabled>
                                                        </td>
                                                        <td>
                                                            {{ $item['satuan'] }}
                                                            <input type="hidden" name="barang[{{ $item['id_barang'] }}][satuan]" value="{{ $item['satuan'] }}">
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   class="form-control form-control-sm harga-input" 
                                                                   name="barang[{{ $item['id_barang'] }}][harga_satuan]" 
                                                                   value="{{ $item['harga_satuan'] ?? 0 }}" 
                                                                   min="0"
                                                                   placeholder="0"
                                                                   disabled>
                                                        </td>
                                                        <td class="total-harga-cell">
                                                            <span class="total-item">Rp 0</span>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-secondary">
                                                    <td colspan="5" class="text-right"><strong>Grand Total:</strong></td>
                                                    <td><strong id="grandTotal">Rp 0</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-12 text-right">
                                            <span class="mr-3 text-muted" id="selectedCount">0 barang dipilih</span>
                                            <button type="submit" class="btn btn-primary" id="btnSubmitPembelian" disabled>
                                                <i class="fas fa-save mr-1"></i> Simpan Daftar Pembelian
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
        // Init Select2 (jika masih ada select2 lain di halaman)
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        // Format number as currency
        function formatRupiah(angka) {
            return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Calculate individual item total and grand total
        function calculateTotals() {
            let grandTotal = 0;
            let selectedCount = 0;

            $('#tablePembelian tbody tr').each(function() {
                const checkbox = $(this).find('.barang-checkbox');
                const qtyInput = $(this).find('.qty-input');
                const hargaInput = $(this).find('.harga-input');
                const totalCell = $(this).find('.total-item');

                if (checkbox.is(':checked')) {
                    selectedCount++;
                    const qty = parseInt(qtyInput.val()) || 0;
                    const harga = parseFloat(hargaInput.val()) || 0;
                    const total = qty * harga;
                    grandTotal += total;
                    totalCell.text(formatRupiah(total));
                } else {
                    totalCell.text('Rp 0');
                }
            });

            $('#grandTotal').text(formatRupiah(grandTotal));
            $('#selectedCount').text(selectedCount + ' barang dipilih');
            
            // Enable/disable submit button
            if (selectedCount > 0) {
                $('#btnSubmitPembelian').prop('disabled', false);
            } else {
                $('#btnSubmitPembelian').prop('disabled', true);
            }
        }

        // Handle checkbox change
        $(document).on('change', '.barang-checkbox', function() {
            const row = $(this).closest('tr');
            const qtyInput = row.find('.qty-input');
            const hargaInput = row.find('.harga-input');

            if ($(this).is(':checked')) {
                qtyInput.prop('disabled', false);
                hargaInput.prop('disabled', false);
            } else {
                qtyInput.prop('disabled', true);
                hargaInput.prop('disabled', true);
            }

            calculateTotals();
        });

        // Handle "Check All" checkbox
        $('#checkAll').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('.barang-checkbox').each(function() {
                $(this).prop('checked', isChecked);
                const row = $(this).closest('tr');
                row.find('.qty-input').prop('disabled', !isChecked);
                row.find('.harga-input').prop('disabled', !isChecked);
            });
            calculateTotals();
        });

        // Handle qty/harga input change
        $(document).on('input', '.qty-input, .harga-input', function() {
            calculateTotals();
        });

        // Handle form submission
        $('#formPembelianBarang').on('submit', function(e) {
            // Validation: check if at least one item is selected
            const selectedItems = $('.barang-checkbox:checked');
            if (selectedItems.length === 0) {
                e.preventDefault();
                alert('Pilih minimal satu barang untuk dibeli.');
                return false;
            }

            // Validation: check if all selected items have price > 0
            let hasZeroPrice = false;
            selectedItems.each(function() {
                const row = $(this).closest('tr');
                const harga = parseFloat(row.find('.harga-input').val()) || 0;
                if (harga <= 0) {
                    hasZeroPrice = true;
                    return false; // break loop
                }
            });

            if (hasZeroPrice) {
                e.preventDefault();
                alert('Semua barang yang dipilih harus memiliki harga > 0.');
                return false;
            }

            // Continue with form submission
            return true;
        });

        // Handle approve form
        $(document).on('submit', '.approve-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const url = form.attr('action');
            const message = form.data('message') || 'Yakin ingin menyetujui work order ini?';
            showApproveRejectConfirm(url, 'approve', message);
        });

        // Initial calculation on page load
        calculateTotals();
    });
</script>
@endsection