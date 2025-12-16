@extends('purchasing.master')

@section('title', 'Update Harga Permintaan Barang')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Update Harga Permintaan Barang</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('purchasing.dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('purchasing.permintaan-barang') }}">Permintaan Barang</a></div>
            <div class="breadcrumb-item active">Update Harga</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Update Harga Permintaan Barang</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('purchasing.permintaan-barang.update-harga', $permintaan->id_permintaan_barang) }}" method="POST" id="formUpdateHarga">
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
                                        <input type="text" class="form-control" value="{{ $permintaan->suratPengajuan->no_surat_pengajuan ?? '-' }}" readonly>
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <h5>Daftar Barang dan Harga</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Nama Barang</th>
                                            <th>Jumlah</th>
                                            <th>Satuan</th>
                                            <th>Harga Satuan <span class="text-danger">*</span></th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($permintaan->daftarBarang as $barang)
                                        <tr>
                                            <td>{{ $barang->nama_barang }}</td>
                                            <td>{{ $barang->jumlah }}</td>
                                            <td>{{ $barang->satuan }}</td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control harga-barang" 
                                                       name="harga_barang[{{ $barang->id_daftar_barang }}]" 
                                                       value="{{ $barang->estimasi_harga ?? 0 }}" 
                                                       min="0" 
                                                       step="0.01"
                                                       required
                                                       data-jumlah="{{ $barang->jumlah }}">
                                            </td>
                                            <td>
                                                <span class="subtotal">Rp {{ number_format(($barang->estimasi_harga ?? 0) * $barang->jumlah, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class="text-right">Total Harga:</th>
                                            <th>
                                                <span id="totalHarga">Rp {{ number_format($permintaan->total_estimasi_harga ?? 0, 0, ',', '.') }}</span>
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="form-group">
                                <label for="catatan_purchasing">Catatan Purchasing</label>
                                <textarea class="form-control" id="catatan_purchasing" name="catatan_purchasing" rows="3" placeholder="Masukkan catatan jika diperlukan">{{ $permintaan->catatan_purchasing }}</textarea>
                            </div>

                            <div class="form-group text-right">
                                <a href="{{ route('purchasing.permintaan-barang') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Update Harga</button>
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
    $(document).ready(function() {
        // Hitung total saat harga berubah
        $(document).on('input', '.harga-barang', function() {
            calculateTotal();
        });

        function calculateTotal() {
            let total = 0;
            $('.harga-barang').each(function() {
                let harga = parseFloat($(this).val()) || 0;
                let jumlah = parseFloat($(this).data('jumlah')) || 0;
                let subtotal = harga * jumlah;
                total += subtotal;
                
                // Update subtotal per row
                $(this).closest('tr').find('.subtotal').text('Rp ' + subtotal.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
            });
            
            // Update total harga
            $('#totalHarga').text('Rp ' + total.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0}));
        }
    });
</script>
@endsection



