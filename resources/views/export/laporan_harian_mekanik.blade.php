<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Laporan Harian Mekanik</title>
    @if(($format ?? 'preview') !== 'pdf')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @endif
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #f0f0f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            color: #1a1a1a;
        }

        /* ===== WINDOWS 11 HEADER BAR ===== */
        .win11-header {
            background: linear-gradient(180deg, #f9f9f9 0%, #f3f3f3 100%);
            border-bottom: 1px solid #e5e5e5;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .win11-header .title-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .win11-header .file-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #0078D4 0%, #106EBE 100%);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .win11-header .title-text {
            display: flex;
            flex-direction: column;
        }

        .win11-header .title-text h1 {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
            line-height: 1.3;
        }

        .win11-header .title-text span {
            font-size: 11px;
            color: #666;
        }

        .win11-header .actions {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        /* Windows 11 Fluent Buttons */
        .win11-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.15s ease;
            text-decoration: none;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .win11-btn:hover {
            text-decoration: none;
        }

        .win11-btn-primary {
            background: linear-gradient(180deg, #0078D4 0%, #006CBD 100%);
            color: white;
            box-shadow: 0 1px 3px rgba(0, 120, 212, 0.3);
        }

        .win11-btn-primary:hover {
            background: linear-gradient(180deg, #106EBE 0%, #005A9E 100%);
            color: white;
            box-shadow: 0 2px 6px rgba(0, 120, 212, 0.4);
        }

        .win11-btn-danger {
            background: linear-gradient(180deg, #D13438 0%, #C42B30 100%);
            color: white;
            box-shadow: 0 1px 3px rgba(209, 52, 56, 0.3);
        }

        .win11-btn-danger:hover {
            background: linear-gradient(180deg, #C42B30 0%, #A4262C 100%);
            color: white;
        }

        .win11-btn-success {
            background: linear-gradient(180deg, #107C10 0%, #0E6E0E 100%);
            color: white;
            box-shadow: 0 1px 3px rgba(16, 124, 16, 0.3);
        }

        .win11-btn-success:hover {
            background: linear-gradient(180deg, #0E6E0E 0%, #0C5E0C 100%);
            color: white;
        }

        .win11-btn-secondary {
            background: #ffffff;
            color: #1a1a1a;
            border: 1px solid #d1d1d1;
        }

        .win11-btn-secondary:hover {
            background: #f5f5f5;
            border-color: #c1c1c1;
            color: #1a1a1a;
        }

        .win11-btn-close {
            background: transparent;
            color: #666;
            padding: 8px 12px;
        }

        .win11-btn-close:hover {
            background: #e81123;
            color: white;
        }

        /* Separator */
        .win11-separator {
            width: 1px;
            height: 24px;
            background: #d1d1d1;
            margin: 0 4px;
        }

        /* ===== PREVIEW CONTAINER ===== */
        .preview-container {
            display: flex;
            justify-content: center;
            padding: 24px;
            min-height: calc(100vh - 60px);
            background: linear-gradient(180deg, #e8e8e8 0%, #f0f0f0 100%);
        }

        /* ===== DOCUMENT PREVIEW ===== */
        .document-preview {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04);
            max-width: 1200px;
            width: 100%;
            overflow: hidden;
        }

        .document-content {
            padding: 32px;
            background-color: #ffffff;
        }

        /* ===== DOCUMENT HEADER ===== */
        .preview-header {
            margin-bottom: 24px;
            width: 100%;
            text-align: center;
            border-bottom: 2px solid #1a1a1a;
            padding-bottom: 16px;
        }

        .preview-header-wrapper {
            display: inline-flex;
            align-items: flex-start;
            gap: 16px;
            vertical-align: top;
        }

        .preview-header .company-logo {
            width: 64px;
            height: 64px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .preview-header .header-content {
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .preview-header .company-name {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #1a1a1a;
            letter-spacing: 0.5px;
        }

        .preview-header .report-title {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
            color: #1a1a1a;
        }

        .preview-header .period {
            font-size: 12px;
            font-weight: 400;
            color: #666;
        }

        /* ===== TABLE STYLES ===== */
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background-color: #ffffff;
            border: 2px solid #1a1a1a;
            font-size: 11px;
            border-radius: 6px;
            overflow: hidden;
        }

        .preview-table th,
        .preview-table td {
            border: 2px solid #1a1a1a;
            padding: 10px 12px;
            vertical-align: top;
            line-height: 1.5;
        }

        .preview-table th {
            background: linear-gradient(180deg, #f8f8f8 0%, #f0f0f0 100%);
            color: #1a1a1a;
            font-weight: 600;
            text-align: center;
            font-size: 11px;
            padding: 12px;
            border-bottom: 3px solid #1a1a1a;
        }

        .preview-table td {
            text-align: left;
            font-size: 10px;
            background-color: #ffffff;
        }

        .preview-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .preview-table tbody tr:hover {
            background-color: #f5f9ff;
        }

        .date-cell { width: 15%; text-align: center; font-weight: 600; vertical-align: middle; }
        .unit-cell { width: 12%; }
        .complaint-cell { width: 15%; }
        .cause-cell { width: 15%; }
        .start-date-cell { width: 15%; }
        .end-date-cell { width: 15%; }
        .action-cell { width: 25%; }

        /* ===== SIGNATURE SECTION ===== */
        .signature-section {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding: 12px;
            vertical-align: top;
        }

        .signature-label {
            font-weight: 600;
            text-decoration: underline;
            margin-bottom: 16px;
            font-size: 11px;
            color: #1a1a1a;
        }

        .signature-space {
            height: 60px;
            border-bottom: 1px solid #1a1a1a;
            margin: 0 auto;
            width: 200px;
        }

        .signature-name {
            font-weight: 600;
            font-size: 11px;
            margin-top: 12px;
            color: #1a1a1a;
        }

        .signature-title {
            font-size: 10px;
            margin-top: 4px;
            color: #666;
        }

        /* ===== PDF EXPORT STYLES ===== */
        body.pdf-export {
            background: #ffffff;
            margin: 0;
            padding: 0;
        }

        body.pdf-export .win11-header {
            display: none;
        }

        body.pdf-export .preview-container {
            padding: 0;
            min-height: auto;
            background: #ffffff;
        }

        body.pdf-export .document-preview {
            margin: 0;
            border-radius: 0;
            box-shadow: none;
        }

        body.pdf-export .document-content {
            padding: 10mm;
        }

        body.pdf-export .preview-header { margin-bottom: 8mm; }
        body.pdf-export .preview-header .company-logo { width: 50px; height: 50px; }
        body.pdf-export .preview-header .company-name { font-size: 11px; }
        body.pdf-export .preview-header .report-title { font-size: 10px; }
        body.pdf-export .preview-header .period { font-size: 9px; }
        body.pdf-export .preview-table { margin-bottom: 8mm; font-size: 8px; }
        body.pdf-export .preview-table th, body.pdf-export .preview-table td { padding: 6px 8px; font-size: 8px; }
        body.pdf-export .signature-section { margin-top: 8mm; }
        body.pdf-export .signature-label { font-size: 9px; }
        body.pdf-export .signature-space { height: 40px; }
        body.pdf-export .signature-name { font-size: 9px; }
        body.pdf-export .signature-title { font-size: 8px; }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            body { background: #ffffff !important; }
            .win11-header { display: none !important; }
            .preview-container { padding: 0 !important; background: #ffffff !important; }
            .document-preview { margin: 0 !important; box-shadow: none !important; border-radius: 0 !important; }
            .document-content { padding: 10mm !important; }
        }
    </style>
</head>
<body class="{{ ($format ?? 'preview') === 'pdf' ? 'pdf-export' : '' }}">
    @if(($format ?? 'preview') !== 'pdf')
    <!-- Windows 11 Header Bar -->
    <div class="win11-header">
        <div class="title-section">
            <div class="file-icon">
                <i class="fas fa-file-lines"></i>
            </div>
            <div class="title-text">
                <h1>Laporan Harian Mekanik</h1>
                <span>Periode: {{ $periode ?? 'Semua Data' }}</span>
            </div>
        </div>
        <div class="actions">
            <a href="{{ route('kadivmekanik.laporan-harian-mekanik.download.pdf') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="win11-btn win11-btn-danger">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('kadivmekanik.laporan-harian-mekanik.download.excel') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" class="win11-btn win11-btn-success">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <div class="win11-separator"></div>
            <button class="win11-btn win11-btn-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
            <button class="win11-btn win11-btn-close" onclick="window.close()">
                <i class="fas fa-xmark"></i>
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
                            <th class="start-date-cell">Mulai Reparasi</th>
                            <th class="end-date-cell">Selesai Reparasi</th>
                            <th class="action-cell">Tindakan Perbaikan</th>
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
                                                    {{ \Carbon\Carbon::parse($report['tanggal_mulai'])->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}
                                                @endif
                                            </td>
                                            <td class="end-date-cell">
                                                @if(!empty($report['tanggal_selesai']))
                                                    {{ \Carbon\Carbon::parse($report['tanggal_selesai'])->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}
                                                @endif
                                            </td>
                                            <td class="action-cell">{{ $report['tindakan_perbaikan'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @else
                                    @foreach($reports as $report)
                                        <tr>
                                            <td class="date-cell">{{ $dateFormatted }}</td>
                                            <td class="unit-cell">{{ $report['nama_unit'] ?? '' }}</td>
                                            <td class="complaint-cell">{{ $report['keluhan_kerusakan'] ?? '' }}</td>
                                            <td class="cause-cell">{{ $report['penyebab_kerusakan'] ?? '' }}</td>
                                            <td class="start-date-cell">
                                                @if(!empty($report['tanggal_mulai']))
                                                    {{ \Carbon\Carbon::parse($report['tanggal_mulai'])->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}
                                                @endif
                                            </td>
                                            <td class="end-date-cell">
                                                @if(!empty($report['tanggal_selesai']))
                                                    {{ \Carbon\Carbon::parse($report['tanggal_selesai'])->locale('id')->isoFormat('dddd, DD/MM/YYYY') }}
                                                @endif
                                            </td>
                                            <td class="action-cell">{{ $report['tindakan_perbaikan'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 32px; color: #666; font-style: italic;">
                                    <i class="fas fa-inbox" style="font-size: 24px; margin-bottom: 8px; display: block; color: #ccc;"></i>
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
