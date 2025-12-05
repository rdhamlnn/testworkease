<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class LaporanHarianMekanikExport
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

        // Set title
        $sheet->setCellValue('A1', 'PT. KALIMANTAN CONCRETE ENGINEERING');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Set report title
        $sheet->setCellValue('A2', 'LAPORAN PERBAIKAN MEKANIK');
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Set period
        $sheet->setCellValue('A3', 'PERIODE: ' . str_replace('_', ' ', $this->periode));
        $sheet->mergeCells('A3:G3');
        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Add empty row
        $sheet->insertNewRowBefore(4, 1);
        
        // Set headers
        $headers = [
            'Hari/Tanggal',
            'Nama Unit',
            'Keluhan/Kerusakan',
            'Penyebab Kerusakan',
            'Mulai Reparasi Hari/Tanggal',
            'Selesai Reparasi Hari/Tanggal',
            'Tindakan Perbaikan dari Mekanik'
        ];
        
        $sheet->fromArray($headers, null, 'A5');
        
        // Style headers
        $sheet->getStyle('A5:G5')->getFont()->setBold(true);
        $sheet->getStyle('A5:G5')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        $sheet->getStyle('A5:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Add data
        $row = 6;
        $dayNames = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jum\'at', 6 => 'Sabtu', 7 => 'Minggu'
        ];

        foreach ($this->data as $laporan) {
            $tanggal = \Carbon\Carbon::parse($laporan->tanggal);
            $hariTanggal = ($dayNames[$tanggal->dayOfWeek] ?? '') . ', ' . $tanggal->format('d/m/Y');

            $mulaiReparasi = '';
            if ($laporan->tanggal_mulai) {
                $mulai = \Carbon\Carbon::parse($laporan->tanggal_mulai);
                $mulaiReparasi = ($dayNames[$mulai->dayOfWeek] ?? '') . ', ' . $mulai->format('d/m/Y');
            }

            $selesaiReparasi = '';
            if ($laporan->tanggal_selesai) {
                $selesai = \Carbon\Carbon::parse($laporan->tanggal_selesai);
                $selesaiReparasi = ($dayNames[$selesai->dayOfWeek] ?? '') . ', ' . $selesai->format('d/m/Y');
            }

            $sheet->setCellValue('A' . $row, $hariTanggal);
            $sheet->setCellValue('B' . $row, $laporan->nama_unit ?? '');
            $sheet->setCellValue('C' . $row, $laporan->keluhan_kerusakan ?? '');
            $sheet->setCellValue('D' . $row, $laporan->penyebab_kerusakan ?? '');
            $sheet->setCellValue('E' . $row, $mulaiReparasi);
            $sheet->setCellValue('F' . $row, $selesaiReparasi);
            $sheet->setCellValue('G' . $row, $laporan->tindakan_perbaikan ?? '');
            
            $row++;
        }
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(25);
        $sheet->getColumnDimension('G')->setWidth(30);
        
        // Auto-adjust column widths
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Set default filename if not provided
        if (!$filename) {
            $filename = 'laporan_harian_mekanik_' . date('Y-m-d_H-i-s') . '.xlsx';
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
