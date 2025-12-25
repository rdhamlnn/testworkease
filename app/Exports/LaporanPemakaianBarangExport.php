<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanPemakaianBarangExport
{
    protected $data;
    protected $periode;

    public function __construct($data, $periode = 'Semua_Data')
    {
        $this->data = $data;
        $this->periode = $periode;
    }

    public function download($filename = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Insert header rows
        $sheet->insertNewRowBefore(1, 4);

        // Set company name
        $sheet->setCellValue('A1', 'PT. KALIMANTAN CONCRETE ENGINEERING');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set report title
        $sheet->setCellValue('A2', 'LAPORAN PEMAKAIAN BARANG');
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set period
        $sheet->setCellValue('A3', 'PERIODE: ' . str_replace('_', ' ', $this->periode));
        $sheet->mergeCells('A3:I3');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Set headers
        $headers = [
            'No',
            'Tanggal',
            'Sparepart/Material/Jasa',
            'Kode Unit',
            'Jumlah',
            'Bentuk Satuan',
            'Harga Satuan',
            'Total Harga',
            'Keterangan',
        ];
        
        $sheet->fromArray($headers, null, 'A5');
        
        // Style headers
        $headerRow = 5;
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');

        // Add data
        $row = 6;
        $no = 0;

        foreach ($this->data as $item) {
            $no++;
            
            $tanggal = \Carbon\Carbon::parse($item->tanggal ?? now())->format('d/m/Y');

            $hargaSatuan = (float) ($item->harga_satuan ?? 0);
            $total = isset($item->total_harga) && $item->total_harga !== null && $item->total_harga !== ''
                ? (float) $item->total_harga
                : ((float) ($item->jumlah ?? 0) * $hargaSatuan);

            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $tanggal);
            $sheet->setCellValue('C' . $row, $item->nama_barang ?? '-');
            $sheet->setCellValue('D' . $row, $item->kode_unit ?? '-');
            $sheet->setCellValue('E' . $row, (float) ($item->jumlah ?? 0));
            $sheet->setCellValue('F' . $row, $item->bentuk_satuan ?? '-');
            $sheet->setCellValue('G' . $row, $hargaSatuan);
            $sheet->setCellValue('H' . $row, $total);
            $sheet->setCellValue('I' . $row, $item->keterangan ?? '-');
            
            // Format angka untuk harga
            $sheet->getStyle('G' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            
            $row++;
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(16);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(16);
        $sheet->getColumnDimension('I')->setWidth(25);
        
        // Add total row
        $highestRow = $sheet->getHighestRow();
        if ($highestRow >= 6) {
            $totalRow = $highestRow + 1;
            $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
            $sheet->setCellValue("G{$totalRow}", 'JUMLAH');
            $sheet->getStyle("G{$totalRow}")->getFont()->setBold(true);
            $sheet->setCellValue("H{$totalRow}", "=SUM(H6:H{$highestRow})");
            $sheet->getStyle("H{$totalRow}")->getFont()->setBold(true);
            $sheet->getStyle("H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
        }
        
        // Apply borders to the entire table (header + data + total row)
        $lastRow = $sheet->getHighestRow();
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle("A5:I{$lastRow}")->applyFromArray($borderStyle);
        
        // Set default filename if not provided
        if (!$filename) {
            $filename = 'laporan_pemakaian_barang_' . date('Y-m-d_H-i-s') . '.xlsx';
        }
        
        // Create writer and return response
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
