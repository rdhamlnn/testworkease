<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Work Order</title>
    

    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 40px;
            font-size: 12pt;
        }

        /* ===== HEADER ===== */
        .header {
            position: relative;
            height: 110px; /* ruang khusus untuk logo */
            margin-bottom: 5px;
        }

        .header img {
            position: absolute;
            top: 0;
            left: 0;
            width: 110px;
            height: auto;
        }

        .text-header {
            text-align: center;
            padding-top: 10px;
        }

        .main-title {
            font-weight: bold;
            font-size: 14pt;
        }

        .sub-title {
            font-weight: bold;
            font-size: 12pt;
        }

        /* Garis di bawah header */
        .header-line {
            border-top: 2px solid black;
            margin-top: 5px;
        }

        /* ===== INFO TABLE ===== */
        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table.info td {
            padding: 5px 0;
            vertical-align: top;
        }

        table.info td.label {
            width: 160px;
        }

        /* ===== Dokumentasi ===== */
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 15px;
        }

        .dokumentasi-box {
            width: 100%;
            height: 160px;
            border: 1px solid black;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 5px;
        }

        .dokumentasi-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        /* ===== Tanda Tangan ===== */
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }

        .signature {
            width: 45%;
        }

        .signature .title {
            font-weight: bold;
            text-decoration: underline;
            color: red;
        }

        .signature p {
            margin: 5px 0;
        }

        /* ==== PRINT MODE ==== */
       @media print {
    .header img {
        position: static !important;
        display: block !important;
    }

    .dokumentasi-box img {
        display: block !important;
        visibility: visible !important;
    }
}

            .btn-print {
                display: none !important;
            }
            
        }
        

        /* Tombol print */
        .btn-print {
            display: block;
            background: #1B3C88;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            margin: 40px auto 0 auto;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <img src="data:image/png;base64,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" alt="KCE Logo">
        <div class="text-header">
            <div class="main-title">WORK ORDER</div>
            <div class="sub-title">PT. KALIMANTAN CONCRETE ENGINEERING</div>
        </div>
    </div>

    <!-- GARIS DI BAWAH HEADER -->
    <div class="header-line"></div>

    <!-- INFO -->
    <table class="info">
        <tr>
            <td class="label">NO. WO</td>
            <td>:</td>
            <td>{{ $wo->no_surat_pengajuan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">DITUJUKAN</td>
            <td>:</td>
            <td>{{ $wo->ditujukan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">JENIS WO</td>
            <td>:</td>
            <td>{{ $wo->jenisWorkOrder->nama_jenis_wo ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">HARI/TANGGAL</td>
            <td>:</td>
            <td>{{ \Carbon\Carbon::parse($wo->tanggal)->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}</td>
        </tr>
        <tr>
            <td class="label">PENGAJU</td>
            <td>:</td>
            <td>{{ $wo->divisi_pengaju ?? '-' }}</td>
        </tr>
        @php
            $jenisWo = strtolower($wo->jenisWorkOrder->nama_jenis_wo ?? '');
        @endphp
        @if($jenisWo === 'perbaikan')
        <tr>
            <td class="label">NAMA UNIT/CODE</td>
            <td>:</td>
            <td>{{ $wo->unit ?? '-' }}</td>
        </tr>
        @endif
        <tr>
            <td class="label">URAIAN</td>
            <td>:</td>
            <td>{{ $wo->uraian ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">STATUS</td>
            <td>:</td>
               <td>
                                        @if($wo->status_text == 'Disetujui')
                                            <span class="badge badge-success">{{ $wo->status_text }}</span>
                                        @elseif($wo->status_text == 'Ditolak')
                                            <span class="badge badge-danger">{{ $wo->status_text }}</span>
                                        @elseif($wo->status_text == 'Dibaca')
                                            <span class="badge badge-info">{{ $wo->status_text }}</span>
                                        @else
                                            <span class="badge badge-warning">{{ $wo->status_text }}</span>
                                        @endif
                                    </td>
        </tr>
    </table>

    {{-- TABEL DAFTAR BARANG - Hanya untuk WO Pembelian --}}
    @php
        $barangItems = collect();
        $hasBarangData = false;
        
        // Prioritas 1: Ambil dari permintaanBarang.daftarBarang
        if ($wo->permintaanBarang && $wo->permintaanBarang->daftarBarang && $wo->permintaanBarang->daftarBarang->count() > 0) {
            $hasBarangData = true;
            foreach ($wo->permintaanBarang->daftarBarang as $item) {
                $barangItems->push([
                    'nama_barang' => $item->nama_barang ?? '-',
                    'jumlah' => $item->jumlah ?? 1,
                    'satuan' => $item->satuan ?? '-',
                    'estimasi_harga' => $item->estimasi_harga ?? 0,
                ]);
            }
        }
        // Prioritas 2: Parse dari field unit (format: "Filter Oli (qty: 1), Belt (qty: 2)")
        elseif ($jenisWo === 'pembelian' && $wo->unit && is_string($wo->unit)) {
            $unitString = $wo->unit;
            preg_match_all('/([^,]+?)(?:\s*\(qty:\s*(\d+)\))?(?:,|$)/i', $unitString, $matches, PREG_SET_ORDER);
            
            // Ambil nama barang untuk lookup satuan dari database
            $namaBarangList = [];
            foreach ($matches as $match) {
                $namaBarang = trim($match[1]);
                if (!empty($namaBarang)) {
                    $namaBarangList[] = $namaBarang;
                }
            }
            
            // Lookup satuan dari tabel DaftarBarang
            $satuanLookup = [];
            if (!empty($namaBarangList)) {
                $masterBarangList = \App\Models\DaftarBarang::whereIn('nama_barang', $namaBarangList)->get();
                foreach ($masterBarangList as $master) {
                    $satuanLookup[$master->nama_barang] = $master->satuan ?? '-';
                }
            }
            
            foreach ($matches as $match) {
                $namaBarang = trim($match[1]);
                $qty = isset($match[2]) ? (int)$match[2] : 1;
                
                if (!empty($namaBarang)) {
                    $hasBarangData = true;
                    $barangItems->push([
                        'nama_barang' => $namaBarang,
                        'jumlah' => $qty,
                        'satuan' => $satuanLookup[$namaBarang] ?? '-',
                        'estimasi_harga' => 0,
                    ]);
                }
            }
        }
        // Prioritas 3: Ambil dari harga_barang JSON column
        elseif ($jenisWo === 'pembelian' && $wo->harga_barang && is_array($wo->harga_barang) && count($wo->harga_barang) > 0) {
            $hasBarangData = true;
            foreach ($wo->harga_barang as $item) {
                $barangItems->push([
                    'nama_barang' => $item['nama_barang'] ?? '-',
                    'jumlah' => $item['qty'] ?? $item['jumlah'] ?? 1,
                    'satuan' => $item['satuan'] ?? '-',
                    'estimasi_harga' => ($item['harga'] ?? 0) * ($item['qty'] ?? $item['jumlah'] ?? 1),
                ]);
            }
        }
    @endphp
    
    @if($jenisWo === 'pembelian' && $hasBarangData && $barangItems->count() > 0)
    <div class="section-title" style="margin-top: 20px;">DAFTAR BARANG :</div>
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="border: 1px solid black; padding: 8px; text-align: center; width: 40px;">No</th>
                <th style="border: 1px solid black; padding: 8px; text-align: left;">Nama Barang</th>
                <th style="border: 1px solid black; padding: 8px; text-align: center; width: 60px;">Qty</th>
                <th style="border: 1px solid black; padding: 8px; text-align: center; width: 70px;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($barangItems as $index => $item)
            <tr>
                <td style="border: 1px solid black; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                <td style="border: 1px solid black; padding: 8px;">{{ $item['nama_barang'] }}</td>
                <td style="border: 1px solid black; padding: 8px; text-align: center;">{{ $item['jumlah'] }}</td>
                <td style="border: 1px solid black; padding: 8px; text-align: center;">{{ $item['satuan'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- DOKUMENTASI -->
    <div class="section-title">DOKUMENTASI :</div>
    <div class="dokumentasi-box">
        @if($wo->dokumentasi && !empty($wo->dokumentasi))
            @php
                $imagePath = storage_path('app/public/' . $wo->dokumentasi);
                $imageExists = file_exists($imagePath);
            @endphp
            @if($imageExists)
                <img src="{{ $imagePath }}">
            @else
                <p style="text-align: center; font-size: 24px; margin: 0; padding-top: 60px;">-</p>
            @endif
        @else
            <p style="text-align: center; font-size: 24px; margin: 0; padding-top: 60px;">-</p>
        @endif
    </div>

    <!-- TANDA TANGAN -->
    <table style="width:100%; margin-top:40px; text-align:center;">
    <tr>
        <td><u><b>Diketahui Oleh:</b></u></td>
        <td><u><b>Dibuat Oleh:</b></u></td>
    </tr>

    <tr>
        <td style="height:90px;"></td>
        <td style="height:90px;"></td>
    </tr>

    <tr>
        <td><strong>{{ $diketahuiOleh->karyawan->nama_lengkap ?? '-' }}</strong></td>
        <td><strong>{{ $dibuatOleh->karyawan->nama_lengkap ?? '-' }}</strong></td>
    </tr>

    <tr>
        <td>Koor ({{ $diketahuiOleh->divisi->nama_divisi ?? '-' }})</td>
        <td>Koor ({{ $dibuatOleh->divisi->nama_divisi ?? '-' }})</td>
    </tr>
</table>


</body>
</html>