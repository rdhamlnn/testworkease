<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Laporan Pemakaian Barang</title>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            color: #000000;
        }

        /* Header Bar seperti Google Drive */
        .preview-header-bar {
            background-color: #fff;
            border-bottom: 1px solid #e0e0e0;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .preview-header-bar .title {
            font-size: 18px;
            font-weight: 500;
            color: #202124;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .preview-header-bar .title i {
            color: #5f6368;
        }

        .preview-header-bar .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .preview-header-bar .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s;
            text-decoration: none;
        }

        .preview-header-bar .btn-primary {
            background-color: #1a73e8;
            color: white;
        }

        .preview-header-bar .btn-primary:hover {
            background-color: #1557b0;
        }

        .preview-header-bar .btn-secondary {
            background-color: #f1f3f4;
            color: #202124;
        }

        .preview-header-bar .btn-secondary:hover {
            background-color: #e8eaed;
        }

        .preview-header-bar .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .preview-header-bar .btn-danger:hover {
            background-color: #c82333;
        }

        .preview-header-bar .btn-success {
            background-color: #28a745;
            color: white;
        }

        .preview-header-bar .btn-success:hover {
            background-color: #218838;
        }

        /* Main Container */
        .preview-container {
            display: flex;
            min-height: calc(100vh - 60px);
            background-color: #ffffff;
            padding: 15px;
        }

        /* Document Preview Area */
        .document-preview {
            flex: 1;
            background-color: #ffffff;
            border: none;
            border-radius: 0;
            box-shadow: none;
            overflow: auto;
            position: relative;
        }

        .document-content {
            padding: 20px;
            background-color: #ffffff;
            min-height: 100%;
        }

        .preview-header {
            margin-bottom: 20px;
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 15px;
        }

        .preview-header-wrapper {
            display: inline-flex;
            align-items: flex-start;
            gap: 15px;
            vertical-align: top;
        }

        .preview-header .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            flex-shrink: 0;
            margin-top: 0;
            align-self: flex-start;
        }

        .preview-header .header-content {
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .preview-header .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #000;
            letter-spacing: 0.5px;
        }

        .preview-header .report-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #000;
            letter-spacing: 0.5px;
        }

        .preview-header .period {
            font-size: 11px;
            font-weight: normal;
            margin-bottom: 0;
            color: #000;
        }

        .preview-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #ffffff;
            border: 1px solid #000000;
            font-size: 10px;
        }

        .preview-table th,
        .preview-table td {
            border: 1px solid #000000;
            padding: 8px 10px;
            vertical-align: top;
            line-height: 1.4;
        }

        .preview-table th {
            background-color: #e8e8e8;
            color: #000000;
            font-weight: bold;
            text-align: center !important;
            font-size: 11px;
            padding: 10px 12px;
            white-space: nowrap;
        }

        .preview-table td {
            text-align: left;
            font-size: 10px;
            background-color: #ffffff;
            word-wrap: break-word;
        }

        .preview-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .preview-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .currency {
            text-align: right;
        }

        /* PDF Specific Styles - Ensure 1 page */
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        body.pdf-export {
            margin: 0;
            padding: 0;
        }

        body.pdf-export .preview-header-bar {
            display: none;
        }

        body.pdf-export .preview-container {
            padding: 0;
            min-height: auto;
        }

        body.pdf-export .document-preview {
            margin: 0;
            border-radius: 0;
            box-shadow: none;
        }

        body.pdf-export .document-content {
            padding: 7mm !important;
        }

        body.pdf-export .preview-header {
            margin-bottom: 5mm !important;
            padding-bottom: 5px !important;
        }

        body.pdf-export .preview-header-wrapper {
            gap: 6px !important;
            align-items: flex-start;
        }

        body.pdf-export .preview-header .company-logo {
            width: 40px !important;
            height: 40px !important;
            flex-shrink: 0;
            align-self: flex-start;
        }

        body.pdf-export .preview-header .company-name {
            font-size: 10px !important;
            line-height: 1.2 !important;
            margin-bottom: 2px !important;
        }

        body.pdf-export .preview-header .report-title {
            font-size: 9px !important;
            line-height: 1.2 !important;
            margin-bottom: 2px !important;
        }

        body.pdf-export .preview-header .period {
            font-size: 8px !important;
            line-height: 1.2 !important;
        }

        body.pdf-export .preview-table {
            margin-bottom: 5mm !important;
            border-collapse: separate;
            border-spacing: 0;
            page-break-inside: avoid !important;
            font-size: 7px !important;
        }

        body.pdf-export .preview-table th,
        body.pdf-export .preview-table td {
            padding: 4px 6px !important;
            font-size: 7px !important;
            line-height: 1.3 !important;
        }

        body.pdf-export .preview-table th {
            font-size: 7px !important;
            padding: 5px 7px !important;
        }

        body.pdf-export .preview-table tbody tr {
            page-break-inside: avoid;
        }

        body.pdf-export .preview-table tfoot {
            page-break-inside: avoid !important;
        }

        body.pdf-export .preview-table tfoot td {
            padding: 4px 6px !important;
            font-size: 7px !important;
        }

        body.pdf-export .document-content > div:last-child {
            page-break-inside: avoid !important;
            margin-top: 5mm !important;
        }

        body.pdf-export .document-content > div:last-child table {
            margin-top: 0 !important;
        }

        body.pdf-export .document-content > div:last-child table td {
            padding: 5px !important;
            font-size: 7px !important;
        }

        body.pdf-export .document-content > div:last-child table div {
            font-size: 7px !important;
            line-height: 1.3 !important;
        }

        /* Print-only: hide control panel and UI, keep only the document */
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                background-color: #ffffff !important;
                font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif !important;
                color: #000000 !important;
            }

            .preview-header-bar {
                display: none !important;
            }

            .preview-container { 
                display: block !important; 
                background-color: #ffffff !important;
            }
            .document-preview { 
                margin: 0 !important; 
                box-shadow: none !important; 
                border-radius: 0 !important; 
                border: none !important;
                background-color: #ffffff !important;
            }
            .document-content { 
                padding: 10mm !important; 
                background-color: #ffffff !important;
            }

            .preview-header {
                margin-bottom: 8mm !important;
                border-bottom: 2px solid #000000 !important;
                padding-bottom: 10px !important;
            }

            .preview-header-wrapper {
                gap: 8px !important;
                align-items: flex-start !important;
            }

            .preview-header .company-logo {
                width: 45px !important;
                height: 45px !important;
                flex-shrink: 0 !important;
                align-self: flex-start !important;
            }

            .preview-header .company-name {
                font-size: 11px !important;
            }

            .preview-header .report-title {
                font-size: 10px !important;
            }

            .preview-header .period {
                font-size: 9px !important;
            }

            .preview-table {
                margin-bottom: 8mm !important;
                border: 1px solid #000000 !important;
                background-color: #ffffff !important;
                page-break-inside: avoid !important;
            }

            .preview-table th,
            .preview-table td {
                border: 1px solid #000000 !important;
                padding: 6px 8px !important;
                font-size: 9px !important;
                line-height: 1.4 !important;
            }

            .preview-table th {
                font-size: 9px !important;
                padding: 8px 10px !important;
                background-color: #e8e8e8 !important;
                color: #000000 !important;
            }

            .preview-table td {
                background-color: #ffffff !important;
                color: #000000 !important;
            }

            .preview-table tbody tr {
                page-break-inside: avoid !important;
            }

            .preview-table tbody tr:nth-child(even) {
                background-color: #f9f9f9 !important;
            }

            .preview-table tfoot {
                page-break-inside: avoid !important;
            }

            .document-content > div:last-child {
                page-break-inside: avoid !important;
            }

            html, body {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="{{ ($format ?? 'preview') === 'pdf' ? 'pdf-export' : '' }}">
    @if(($format ?? 'preview') !== 'pdf')
    <!-- Header Bar seperti Google Drive -->
    <div class="preview-header-bar">
        <div class="title">
            <i class="fa fa-file-text-o"></i>
            <span>Preview Laporan Pemakaian Barang</span>
        </div>
        <div class="actions">
            <a href="/download-laporan-pemakaian-barang-pdf{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-danger">
                <i class="fa fa-file-pdf"></i> Download PDF
            </a>
            <a href="/download-laporan-pemakaian-barang-excel{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success">
                <i class="fa fa-file-excel"></i> Download Excel
            </a>
            <button class="btn btn-secondary" onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
            <button class="btn btn-secondary" onclick="window.close()">
                <i class="fa fa-times"></i> Tutup
            </button>
        </div>
    </div>
    @endif

    <!-- Main Container -->
    <div class="preview-container">
        <!-- Document Preview -->
        <div class="document-preview">
                <div class="preview-header">
                    <div class="preview-header-wrapper">
                        @php
                            if(($format ?? 'preview') === 'pdf') {
                                $logoPath = public_path('assets/img/KCE.png');
                                if (file_exists($logoPath)) {
                                    $logoBase64 = base64_encode(file_get_contents($logoPath));
                                    echo '<img src="data:image/png;base64,' . $logoBase64 . '" alt="Logo" class="company-logo">';
                                }
                            } else {
                                echo '<img src="' . asset('assets/img/KCE.png') . '" alt="Logo" class="company-logo">';
                            }
                        @endphp
                        <div class="header-content">
                            <div class="company-name">PT. KALIMANTAN CONCRETE ENGINEERING</div>
                            <div class="report-title">LAPORAN PEMAKAIAN BARANG</div>
                            <div class="period">PERIODE: {{ $periode ?? 'Semua Data' }}</div>
                        </div>
                    </div>
                </div>

                <table class="preview-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No</th>
                            <th style="width: 12%;">Tanggal</th>
                            <th style="width: 25%;">Sparepart/Material/Jasa</th>
                            <th style="width: 10%;">Kode Unit</th>
                            <th style="width: 7%;">Jumlah</th>
                            <th style="width: 8%;">Bentuk Satuan</th>
                            <th style="width: 12%;">Harga Satuan</th>
                            <th style="width: 12%;">Total Harga</th>
                            <th style="width: 9%;">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotal = 0;
                        @endphp
                        @forelse($data as $i => $row)
                            @php
                                $total = isset($row->total_harga) && $row->total_harga !== null && $row->total_harga !== '' 
                                    ? (float)$row->total_harga 
                                    : ((float)($row->jumlah ?? 0) * (float)($row->harga_satuan ?? 0));
                                $grandTotal += $total;
                            @endphp
                            <tr>
                                <td style="text-align: center;">{{ $i + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($row->tanggal ?? now())->format('d/m/Y') }}</td>
                                <td>{{ $row->nama_barang ?? '-' }}</td>
                                <td>{{ $row->kode_unit ?? '-' }}</td>
                                <td style="text-align: center;">{{ $row->jumlah ?? '-' }}</td>
                                <td style="text-align: center;">{{ $row->bentuk_satuan ?? '-' }}</td>
                                <td class="currency">Rp {{ number_format($row->harga_satuan ?? 0, 0, ',', '.') }}</td>
                                <td class="currency">Rp {{ number_format($total, 0, ',', '.') }}</td>
                                <td>{{ $row->keterangan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                                    Tidak ada data laporan untuk periode yang dipilih
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align: right; font-weight: bold; padding: 10px;">JUMLAH</td>
                            <td colspan="2" class="currency" style="font-weight: bold; padding: 10px;">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                 <!-- TANDA TANGAN -->
                <div style="margin-top: 30px; border-top: 1px solid #000000; padding-top: 20px;">
                    <table style="width:100%; text-align:center; border-collapse: collapse;">
                        <tr>
                            <td style="width:50%; padding: 10px;">
                                <div style="font-weight: bold; text-decoration: underline; margin-bottom: 15px; font-size: 10px;">Menyetujui:</div>
                                <div style="height: 50px; border-bottom: 1px solid #000000; margin: 0 auto; width: 200px;"></div>
                                <div style="font-weight: bold; margin-top: 10px; font-size: 10px;">Jony Rakhman, S.T.</div>
                                <div style="font-size: 9px; margin-top: 5px;">Direktur</div>
                            </td>
                            <td style="width:50%; padding: 10px;">
                                <div style="font-weight: bold; text-decoration: underline; margin-bottom: 15px; font-size: 10px;">Dibuat Oleh:</div>
                                <div style="height: 50px; border-bottom: 1px solid #000000; margin: 0 auto; width: 200px;"></div>
                                <div style="font-weight: bold; margin-top: 10px; font-size: 10px;">Dewo Kuncoro Putra</div>
                                <div style="font-size: 9px; margin-top: 5px;">Kadiv Mekanik</div>
                            </td>
                        </tr>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
