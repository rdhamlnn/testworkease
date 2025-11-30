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

class LaporanPemakaianBarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $data;
    protected $periode;

    public function __construct($data, $periode = 'Semua_Data')
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
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $tanggal = \Carbon\Carbon::parse($row->tanggal ?? now())->format('d/m/Y');

        $hargaSatuan = (float) ($row->harga_satuan ?? 0);
        $total = isset($row->total_harga) && $row->total_harga !== null && $row->total_harga !== ''
            ? (float) $row->total_harga
            : ((float) ($row->jumlah ?? 0) * $hargaSatuan);

        return [
            $no,
            $tanggal,
            $row->nama_barang ?? '-',
            $row->kode_unit ?? '-',
            (float) ($row->jumlah ?? 0),
            $row->bentuk_satuan ?? '-',
            $hargaSatuan,
            $total,
            $row->keterangan ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 12,
            'C' => 30,
            'D' => 12,
            'E' => 10,
            'F' => 16,
            'G' => 16,
            'H' => 16,
            'I' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Sisipkan header seperti preview
                $sheet->insertNewRowBefore(1, 4);

                $sheet->setCellValue('A1', 'PT. KALIMANTAN CONCRETE ENGINEERING');
                $sheet->mergeCells('A1:I1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->setCellValue('A2', 'LAPORAN PEMAKAIAN BARANG');
                $sheet->mergeCells('A2:I2');
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                $sheet->setCellValue('A3', 'PERIODE: ' . str_replace('_', ' ', $this->periode));
                $sheet->mergeCells('A3:I3');
                $sheet->getStyle('A3')->getFont()->setBold(true);
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // Header kolom tebal dan rata tengah
                $headerRow = 5;
                $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getFont()->setBold(true);
                $sheet->getStyle("A{$headerRow}:I{$headerRow}")->getAlignment()->setHorizontal('center');

                // Format angka untuk harga
                $highestRow = $sheet->getHighestRow();
                if ($highestRow >= 6) {
                    $sheet->getStyle("G6:G{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("H6:H{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');

                    // Baris total sesuai preview (JUMLAH)
                    $totalRow = $highestRow + 1;
                    $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
                    $sheet->setCellValue("G{$totalRow}", 'JUMLAH');
                    $sheet->getStyle("G{$totalRow}")->getFont()->setBold(true);
                    $sheet->setCellValue("H{$totalRow}", "=SUM(H6:H{$highestRow})");
                    $sheet->getStyle("H{$totalRow}")->getFont()->setBold(true);
                    $sheet->getStyle("H{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                }
            },
        ];
    }
}


