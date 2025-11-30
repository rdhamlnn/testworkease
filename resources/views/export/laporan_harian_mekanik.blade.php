<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Laporan Harian Mekanik</title>
    @if(($format ?? 'preview') !== 'pdf')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
    @endif
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #ffffff;
            font-family: Arial, 'Helvetica Neue', Helvetica, sans-serif;
            overflow-x: hidden;
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
        }

        /* Header Document */
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
            margin-top: 0;
            text-transform: uppercase;
            color: #000;
            line-height: 1.3;
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
            color: #000;
        }

        /* Table Styles - Windows Log Format */
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
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
            text-align: center;
            font-size: 11px;
            padding: 10px 12px;
            white-space: nowrap;
        }

        .preview-table td {
            text-align: left;
            font-size: 10px;
            background-color: #ffffff;
            padding: 8px 10px;
            word-wrap: break-word;
        }

        .preview-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .preview-table tbody tr:hover {
            background-color: #f0f0f0;
        }

        .date-cell {
            width: 15%;
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .unit-cell {
            width: 12%;
        }

        .complaint-cell {
            width: 15%;
        }

        .cause-cell {
            width: 15%;
        }

        .start-date-cell {
            width: 15%;
            word-wrap: break-word;
        }

        .end-date-cell {
            width: 15%;
            word-wrap: break-word;
        }

        .action-cell {
            width: 25%;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 30px;
            width: 100%;
            border-top: none;
            padding-top: 20px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 10px;
            vertical-align: top;
        }

        .signature-table .signature-label {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 15px;
            font-size: 10px;
        }

        .signature-table .signature-space {
            height: 50px;
            border-bottom: 1px solid #000000;
            margin: 0 auto;
            width: 200px;
        }

        .signature-table .signature-name {
            font-weight: bold;
            font-size: 10px;
            margin-top: 10px;
        }

        .signature-table .signature-title {
            font-size: 9px;
            margin-top: 5px;
        }

        /* PDF Specific Styles - Ensure 1 page */
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
            padding: 10mm;
        }

        body.pdf-export .preview-header {
            margin-bottom: 8mm;
        }

        body.pdf-export .preview-header-wrapper {
            gap: 8px;
            align-items: flex-start;
        }

        body.pdf-export .preview-header .company-logo {
            width: 50px;
            height: 50px;
            flex-shrink: 0;
            align-self: flex-start;
        }

        body.pdf-export .preview-header .company-name {
            font-size: 11px;
        }

        body.pdf-export .preview-header .report-title {
            font-size: 10px;
        }

        body.pdf-export .preview-header .period {
            font-size: 9px;
        }

        body.pdf-export .preview-table {
            margin-bottom: 8mm;
            border-collapse: separate;
            border-spacing: 0;
            page-break-inside: avoid;
        }

        body.pdf-export .preview-table th,
        body.pdf-export .preview-table td {
            padding: 6px 8px;
            font-size: 8px;
            line-height: 1.4;
        }

        body.pdf-export .preview-table th {
            font-size: 8px;
            padding: 8px 10px;
        }

        body.pdf-export .preview-table tbody tr {
            page-break-inside: avoid;
        }

        body.pdf-export .signature-section {
            margin-top: 8mm;
            page-break-inside: avoid;
        }

        body.pdf-export .signature-table {
            margin-top: 5mm;
        }

        body.pdf-export .signature-table td {
            padding: 4px;
        }

        body.pdf-export .signature-table .signature-label {
            font-size: 9px;
        }

        body.pdf-export .signature-table .signature-space {
            height: 40px;
        }

        body.pdf-export .signature-table .signature-name {
            font-size: 9px;
        }

        body.pdf-export .signature-table .signature-title {
            font-size: 8px;
        }

        body.pdf-export .signature-table td {
            padding: 4px;
        }

        body.pdf-export .signature-table .signature-label {
            font-size: 9px;
        }

        body.pdf-export .signature-table .signature-space {
            height: 40px;
        }

        body.pdf-export .signature-table .signature-name {
            font-size: 9px;
        }

        body.pdf-export .signature-table .signature-title {
            font-size: 8px;
        }

        /* Print Styles - Windows Log Format */
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
                padding: 0 !important;
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
                border-collapse: collapse !important;
                border: 1px solid #000000 !important;
                background-color: #ffffff !important;
            }

            .preview-table th,
            .preview-table td {
                padding: 6px 8px !important;
                font-size: 9px !important;
                line-height: 1.4 !important;
                border: 1px solid #000000 !important;
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

            .preview-table tbody tr:nth-child(even) {
                background-color: #f9f9f9 !important;
            }

            .signature-section {
                margin-top: 8mm !important;
                border-top: 1px solid #000000 !important;
                padding-top: 10px !important;
            }

            .signature-table {
                margin-top: 5mm !important;
            }

            .signature-table td {
                padding: 8px !important;
            }

            .signature-table .signature-label {
                font-size: 9px !important;
            }

            .signature-table .signature-space {
                height: 40px !important;
                border-bottom: 1px solid #000000 !important;
            }

            .signature-table .signature-name {
                font-size: 9px !important;
            }

            .signature-table .signature-title {
                font-size: 8px !important;
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
            <span>Preview Laporan Harian Mekanik</span>
        </div>
        <div class="actions">
            <a href="{{ route('kadivmekanik.laporan-harian-mekanik.download.pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-danger">
                <i class="fa fa-file-pdf"></i> Download PDF
            </a>
            <a href="{{ route('kadivmekanik.laporan-harian-mekanik.download.excel') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="btn btn-success">
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
            <div class="document-content">
                <!-- Header Document -->
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
                            <div class="report-title">LAPORAN PERBAIKAN MEKANIK</div>
                            <div class="period">PERIODE: {{ $periode ?? 'Semua Data' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Table Data -->
                <table class="preview-table">
                    <thead>
                        <tr>
                            <th class="date-cell">Hari/Tanggal</th>
                            <th class="unit-cell">Nama Unit</th>
                            <th class="complaint-cell">Keluhan/Kerusakan</th>
                            <th class="cause-cell">Penyebab Kerusakan</th>
                            <th class="start-date-cell">Mulai Reparasi Hari/Tanggal</th>
                            <th class="end-date-cell">Selesai Reparasi Hari/Tanggal</th>
                            <th class="action-cell">Tindakan Perbaikan dari Mekanik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $groupedData = $data->groupBy(function($item) {
                                return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
                            });
                        @endphp
                        
                        @if($data->count() > 0)
                            @foreach($groupedData as $date => $dayReports)
                                @php
                                    $carbon = \Carbon\Carbon::parse($date)->locale('id');
                                    $dateFormatted = $carbon->isoFormat('dddd, DD/MM/YYYY');
                                    $reports = $dayReports->toArray();
                                    $rowCount = count($reports);
                                @endphp
                                
                                @if($rowCount > 1)
                                    {{-- Multiple entries for the same date --}}
                                    @foreach($reports as $index => $report)
                                        <tr>
                                            @if($index === 0)
                                                <td class="date-cell" rowspan="{{ $rowCount }}">{{ $dateFormatted }}</td>
                                            @endif
                                            <td class="unit-cell">{{ $report['nama_unit'] ?? '' }}</td>
                                            <td class="complaint-cell">{{ $report['keluhan_kerusakan'] ?? '' }}</td>
                                            <td class="cause-cell">{{ $report['penyebab_kerusakan'] ?? '' }}</td>
                                            <td class="start-date-cell">
                                                @if(!empty($report['tanggal_mulai']))
                                                    @php
                                                        $startCarbon = \Carbon\Carbon::parse($report['tanggal_mulai'])->locale('id');
                                                        echo $startCarbon->isoFormat('dddd, DD/MM/YYYY');
                                                    @endphp
                                                @endif
                                            </td>
                                            <td class="end-date-cell">
                                                @if(!empty($report['tanggal_selesai']))
                                                    @php
                                                        $endCarbon = \Carbon\Carbon::parse($report['tanggal_selesai'])->locale('id');
                                                        echo $endCarbon->isoFormat('dddd, DD/MM/YYYY');
                                                    @endphp
                                                @endif
                                            </td>
                                            <td class="action-cell">{{ $report['tindakan_perbaikan'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    {{-- Single entry for the date --}}
                                    @foreach($reports as $report)
                                        <tr>
                                            <td class="date-cell">{{ $dateFormatted }}</td>
                                            <td class="unit-cell">{{ $report['nama_unit'] ?? '' }}</td>
                                            <td class="complaint-cell">{{ $report['keluhan_kerusakan'] ?? '' }}</td>
                                            <td class="cause-cell">{{ $report['penyebab_kerusakan'] ?? '' }}</td>
                                            <td class="start-date-cell">
                                                @if(!empty($report['tanggal_mulai']))
                                                    @php
                                                        $startCarbon = \Carbon\Carbon::parse($report['tanggal_mulai'])->locale('id');
                                                        echo $startCarbon->isoFormat('dddd, DD/MM/YYYY');
                                                    @endphp
                                                @endif
                                            </td>
                                            <td class="end-date-cell">
                                                @if(!empty($report['tanggal_selesai']))
                                                    @php
                                                        $endCarbon = \Carbon\Carbon::parse($report['tanggal_selesai'])->locale('id');
                                                        echo $endCarbon->isoFormat('dddd, DD/MM/YYYY');
                                                    @endphp
                                                @endif
                                            </td>
                                            <td class="action-cell">{{ $report['tindakan_perbaikan'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px; font-style: italic; color: #666;">
                                    Tidak ada data laporan untuk periode yang dipilih
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <!-- Signature Section -->
                <div class="signature-section">
                    <table class="signature-table">
                        <tr>
                            <td>
                                <div class="signature-label">Menyetujui:</div>
                                <div class="signature-space"></div>
                                <div class="signature-name">Jony Rakhman, S.T.</div>
                                <div class="signature-title">Direktur</div>
                            </td>
                            <td>
                                <div class="signature-label">Dibuat Oleh:</div>
                                <div class="signature-space"></div>
                                <div class="signature-name">Dewo Kuncoro Putra</div>
                                <div class="signature-title">Kadiv Mekanik</div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
