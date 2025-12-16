@extends('logistik.master')

@section('title', 'Edit Permintaan Barang')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Edit Permintaan Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('logistik.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('logistik.permintaan-barang') }}">Permintaan Barang</a></div>
            <div class="breadcrumb-item active">Edit Permintaan Barang</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Edit Permintaan Barang</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('logistik.permintaan-barang.update', $permintaan->id_permintaan_barang) }}" method="POST" id="formPermintaanBarang">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Permintaan</label>
                                        <input type="text" class="form-control" value="{{ $permintaan->no_permintaan_barang }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. Work Order</label>
                                        <input type="text" class="form-control" value="{{ $workOrder->no_surat_pengajuan }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tanggal_permintaan">Tanggal Permintaan <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="tanggal_permintaan" name="tanggal_permintaan" value="{{ \Carbon\Carbon::parse($permintaan->tanggal_permintaan)->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Uraian Work Order</label>
                                <textarea class="form-control" rows="3" readonly>{{ $workOrder->uraian }}</textarea>
                            </div>

                            <hr>
                            <h5>Daftar Barang</h5>
                            
                            <div id="daftarBarangContainer">
                                @foreach($permintaan->daftarBarang as $index => $barang)
                                <div class="row mb-3 barang-item">
                                    <div class="col-md-4">
                                        <label>Nama Barang <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control nama-barang" name="barang[{{ $index }}][nama_barang]" value="{{ $barang->nama_barang }}" required placeholder="Masukkan nama barang">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control jumlah-barang" name="barang[{{ $index }}][jumlah]" value="{{ $barang->jumlah }}" min="1" required placeholder="Jumlah">
                                    </div>
                                    <div class="col-md-2">
                                        <label>Satuan <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control satuan-barang" name="barang[{{ $index }}][satuan]" value="{{ $barang->satuan }}" placeholder="pcs/kg" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Estimasi Harga <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control harga-barang" name="barang[{{ $index }}][estimasi_harga]" value="{{ $barang->estimasi_harga }}" min="0" required placeholder="Masukkan estimasi harga">
                                    </div>
                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                        <button type="button" class="btn btn-danger btn-block btn-remove-item" {{ $permintaan->daftarBarang->count() == 1 ? 'style="display:none;"' : '' }}>
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <button type="button" class="btn btn-secondary mb-3" id="btnTambahBarang">
                                <i class="fas fa-plus"></i> Tambah Barang
                            </button>

                            <div class="form-group">
                                <label>Total Estimasi Harga</label>
                                <input type="text" class="form-control" id="totalEstimasiHarga" value="Rp {{ number_format($permintaan->total_estimasi_harga ?? 0, 0, ',', '.') }}" readonly style="font-size: 18px; font-weight: bold;">
                                <small class="form-text text-muted">Total akan dihitung otomatis berdasarkan jumlah dan harga barang</small>
                            </div>

                            <div class="form-group">
                                <label for="catatan_logistik">Catatan Logistik</label>
                                <textarea class="form-control" id="catatan_logistik" name="catatan_logistik" rows="3" placeholder="Masukkan catatan jika diperlukan">{{ $permintaan->catatan_logistik }}</textarea>
                            </div>

                            <div class="form-group text-right">
                                <a href="{{ route('logistik.permintaan-barang') }}" class="btn btn-secondary">Batal</a>
                                <button type="reset" class="btn btn-warning">Reset</button>
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    let itemCount = {{ $permintaan->daftarBarang->count() }};

    $(document).ready(function() {
        calculateTotal();
        
        $('#btnTambahBarang').on('click', function() {
            addBarangItem();
        });

        $(document).on('click', '.btn-remove-item', function() {
            $(this).closest('.barang-item').remove();
            renumberItems();
            calculateTotal();
        });

        $(document).on('input', '.harga-barang, .jumlah-barang', function() {
            calculateTotal();
        });

        $('#formPermintaanBarang').on('submit', function(e) {
            e.preventDefault();
            
            // Convert form data to JSON
            let barangData = [];
            $('.barang-item').each(function() {
                let namaBarang = $(this).find('.nama-barang').val();
                let jumlah = $(this).find('.jumlah-barang').val();
                let satuan = $(this).find('.satuan-barang').val();
                let harga = $(this).find('.harga-barang').val();
                
                if (namaBarang && jumlah && satuan && harga) {
                    barangData.push({
                        nama_barang: namaBarang,
                        jumlah: jumlah,
                        satuan: satuan,
                        estimasi_harga: harga
                    });
                }
            });
            
            // Create hidden input for JSON data
            if ($('#daftarBarangJson').length) {
                $('#daftarBarangJson').remove();
            }
            $('<input>').attr({
                type: 'hidden',
                id: 'daftarBarangJson',
                name: 'daftar_barang',
                value: JSON.stringify(barangData)
            }).appendTo('#formPermintaanBarang');
            
            // Fix total_estimasi_harga - remove currency formatting
            let totalHarga = $('#totalEstimasiHarga').val().replace(/[Rp\s\.]/g, '').replace(',', '.');
            if ($('#totalEstimasiHargaHidden').length) {
                $('#totalEstimasiHargaHidden').remove();
            }
            $('<input>').attr({
                type: 'hidden',
                id: 'totalEstimasiHargaHidden',
                name: 'total_estimasi_harga',
                value: totalHarga
            }).appendTo('#formPermintaanBarang');
            
            // Submit form
            this.submit();
        });
    });

    function addBarangItem() {
        let html = `
            <div class="row mb-3 barang-item">
                <div class="col-md-4">
                    <label>Nama Barang <span class="text-danger">*</span></label>
                    <input type="text" class="form-control nama-barang" name="barang[${itemCount}][nama_barang]" required placeholder="Masukkan nama barang">
                </div>
                <div class="col-md-2">
                    <label>Jumlah <span class="text-danger">*</span></label>
                    <input type="number" class="form-control jumlah-barang" name="barang[${itemCount}][jumlah]" min="1" required placeholder="Jumlah">
                </div>
                <div class="col-md-2">
                    <label>Satuan <span class="text-danger">*</span></label>
                    <input type="text" class="form-control satuan-barang" name="barang[${itemCount}][satuan]" placeholder="pcs/kg" required>
                </div>
                <div class="col-md-3">
                    <label>Estimasi Harga <span class="text-danger">*</span></label>
                    <input type="number" class="form-control harga-barang" name="barang[${itemCount}][estimasi_harga]" min="0" required placeholder="Masukkan estimasi harga">
                </div>
                <div class="col-md-1">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-block btn-remove-item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        $('#daftarBarangContainer').append(html);
        itemCount++;
        
        // Show remove button for all items if more than one
        if ($('.barang-item').length > 1) {
            $('.btn-remove-item').show();
        }
    }

    function renumberItems() {
        $('.barang-item').each(function(index) {
            $(this).find('input').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    name = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', name);
                }
            });
        });
        
        if ($('.barang-item').length === 1) {
            $('.btn-remove-item').hide();
        }
    }

    function calculateTotal() {
        let total = 0;
        $('.barang-item').each(function() {
            let jumlah = parseFloat($(this).find('.jumlah-barang').val()) || 0;
            let harga = parseFloat($(this).find('.harga-barang').val()) || 0;
            total += (jumlah * harga);
        });
        $('#totalEstimasiHarga').val('Rp ' + total.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
    }
</script>
@endsection




