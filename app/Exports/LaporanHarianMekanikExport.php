<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanHarianMekanikExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;
    protected $periode;

    public function __construct($data, $periode)
    {
        $this->data = $data;
        $this->periode = $periode;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Hari/Tanggal',
            'Nama Unit',
            'Keluhan/Kerusakan',
            'Penyebab Kerusakan',
            'Mulai Reparasi Hari/Tanggal',
            'Selesai Reparasi Hari/Tanggal',
            'Tindakan Perbaikan dari Mekanik'
        ];
    }

    public function map($laporan): array
    {
        $dayNames = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
            5 => 'Jum\'at', 6 => 'Sabtu', 7 => 'Minggu'
        ];

        $tanggal = \Carbon\Carbon::parse($laporan->tanggal);
        $hariTanggal = $dayNames[$tanggal->dayOfWeek] ?? '' . ', ' . $tanggal->format('d/m/Y');

        $mulaiReparasi = '';
        if ($laporan->tanggal_mulai) {
            $mulai = \Carbon\Carbon::parse($laporan->tanggal_mulai);
            $mulaiReparasi = $dayNames[$mulai->dayOfWeek] ?? '' . ', ' . $mulai->format('d/m/Y');
        }

        $selesaiReparasi = '';
        if ($laporan->tanggal_selesai) {
            $selesai = \Carbon\Carbon::parse($laporan->tanggal_selesai);
            $selesaiReparasi = $dayNames[$selesai->dayOfWeek] ?? '' . ', ' . $selesai->format('d/m/Y');
        }

        return [
            $hariTanggal,
            $laporan->nama_unit ?? '',
            $laporan->keluhan_kerusakan ?? '',
            $laporan->penyebab_kerusakan ?? '',
            $mulaiReparasi,
            $selesaiReparasi,
            $laporan->tindakan_perbaikan ?? ''
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Hari/Tanggal
            'B' => 15, // Nama Unit
            'C' => 25, // Keluhan/Kerusakan
            'D' => 25, // Penyebab Kerusakan
            'E' => 25, // Mulai Reparasi
            'F' => 25, // Selesai Reparasi
            'G' => 30, // Tindakan Perbaikan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Get the sheet
                $sheet = $event->sheet->getDelegate();
                
                // Set title
                $sheet->setCellValue('A1', 'PT. KALIMANTAN CONCRETE ENGINEERING');
                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
                
                // Set report title
                $sheet->setCellValue('A2', 'LAPORAN PERBAIKAN MEKANIK');
                $sheet->mergeCells('A2:G2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');
                
                // Set period
                $sheet->setCellValue('A3', 'PERIODE: ' . str_replace('_', ' ', $this->periode));
                $sheet->mergeCells('A3:G3');
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');
                
                // Add empty row
                $sheet->insertNewRowBefore(4, 1);
                
                // Move headers to row 5
                $sheet->setCellValue('A5', 'Hari/Tanggal');
                $sheet->setCellValue('B5', 'Nama Unit');
                $sheet->setCellValue('C5', 'Keluhan/Kerusakan');
                $sheet->setCellValue('D5', 'Penyebab Kerusakan');
                $sheet->setCellValue('E5', 'Mulai Reparasi Hari/Tanggal');
                $sheet->setCellValue('F5', 'Selesai Reparasi Hari/Tanggal');
                $sheet->setCellValue('G5', 'Tindakan Perbaikan dari Mekanik');
                
                // Style headers
                $sheet->getStyle('A5:G5')->getFont()->setBold(true);
                $sheet->getStyle('A5:G5')->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
                $sheet->getStyle('A5:G5')->getAlignment()->setHorizontal('center');
                
                // Auto-adjust column widths
                foreach (range('A', 'G') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }
}
