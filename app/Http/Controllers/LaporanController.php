<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SuratPengajuan;
use App\Models\LaporanHarianMekanik;
use App\Models\LaporanPemakaianBarang;
use App\Models\Divisi;
use App\Models\Unit;
use App\Models\Akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\LaporanHarianMekanikExport;
use App\Exports\LaporanPemakaianBarangExport;

class LaporanController extends Controller
{
    use \App\Traits\ReportHelper;



    /**
     * Display laporan harian mekanik page.
     */
    public function harianMekanik(Request $request)
    {
        $query = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            $tahun = $request->tahun ?: Carbon::now('Asia/Makassar')->year;
            $bulan = $request->bulan ?: Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Get filter options
        $tahunOptions = LaporanHarianMekanik::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $bulanOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('admin.laporan_harian_mekanik', compact('data', 'tahunOptions', 'bulanOptions'));
    }

    /**
     * Display laporan pemakaian barang page.
     */
    public function pemakaianBarang(Request $request)
    {
        $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Get filter options
        $tahunOptions = LaporanPemakaianBarang::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $bulanOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        return view('admin.laporan_pemakaian_barang', compact('data', 'tahunOptions', 'bulanOptions'));
    }

    /**
     * Display laporan arsip WO page.
     */
    public function arsipWo(Request $request)
    {
        $query = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun']);

        // Apply filters if provided
        if ($request->has('kategori') && $request->kategori) {
            $query->where('id_verifikator', $request->kategori);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_surat_pengajuan', 'like', "%{$search}%")
                  ->orWhere('divisi_pengaju', 'like', "%{$search}%")
                  ->orWhere('ditujukan', 'like', "%{$search}%")
                  ->orWhere('uraian', 'like', "%{$search}%");
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Get filter options
        $kategoriOptions = [
            '1' => 'Menunggu Verifikasi',
            '2' => 'Disetujui',
            '3' => 'Ditolak'
        ];

        return view('admin.laporan_arsip_wo', compact('data', 'kategoriOptions'));
    }

    /**
     * Filter laporan harian mekanik.
     */
    public function filterHarianMekanik(Request $request)
    {
        $query = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters
        if ($request->has('tahun') && $request->tahun && $request->tahun !== '') {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan && $request->bulan !== '') {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            // Gunakan tahun dan bulan dari request, atau default ke tahun/bulan saat ini
            $tahun = $request->has('tahun') && $request->tahun && $request->tahun !== '' 
                ? (int)$request->tahun 
                : Carbon::now('Asia/Makassar')->year;
            $bulan = $request->has('bulan') && $request->bulan && $request->bulan !== '' 
                ? (int)$request->bulan 
                : Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

        // Format data untuk response
        $formattedData = $data->map(function($item) {
            return [
                'id_laporan_harian_mekanik' => $item->id_laporan_harian_mekanik,
                'tanggal' => $item->tanggal,
                'nama_unit' => $item->nama_unit,
                'keluhan_kerusakan' => $item->keluhan_kerusakan,
                'penyebab_kerusakan' => $item->penyebab_kerusakan,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_selesai' => $item->tanggal_selesai,
                'tindakan_perbaikan' => $item->tindakan_perbaikan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'message' => 'Data berhasil difilter'
        ]);
    }

    /**
     * Filter laporan pemakaian barang.
     */
    public function filterPemakaianBarang(Request $request)
    {
        $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters
        if ($request->has('tahun') && $request->tahun && $request->tahun !== '') {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan && $request->bulan !== '') {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            // Gunakan tahun dan bulan dari request, atau default ke tahun/bulan saat ini
            $tahun = $request->has('tahun') && $request->tahun && $request->tahun !== '' 
                ? (int)$request->tahun 
                : Carbon::now('Asia/Makassar')->year;
            $bulan = $request->has('bulan') && $request->bulan && $request->bulan !== '' 
                ? (int)$request->bulan 
                : Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

        // Format data untuk response
        $formattedData = $data->map(function($item) {
            return [
                'id_laporan_pemakaian_barang' => $item->id_laporan_pemakaian_barang,
                'tanggal' => $item->tanggal,
                'nama_barang' => $item->nama_barang,
                'kode_unit' => $item->kode_unit,
                'jumlah' => $item->jumlah,
                'bentuk_satuan' => $item->bentuk_satuan,
                'harga_satuan' => $item->harga_satuan,
                'total_harga' => $item->total_harga,
                'keterangan' => $item->keterangan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'message' => 'Data berhasil difilter'
        ]);
    }


    /**
     * Filter laporan arsip WO.
     */
    public function filterArsipWo(Request $request)
    {
        $query = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun']);

        // Apply filters
        if ($request->has('kategori') && $request->kategori && $request->kategori !== '' && $request->kategori !== 'semua') {
            // Map kategori string to id_verifikator
            $kategoriMap = [
                'menunggu' => 1,
                'disetujui' => 2,
                'ditolak' => 3
            ];
            
            if (isset($kategoriMap[$request->kategori])) {
                $query->where('id_verifikator', $kategoriMap[$request->kategori]);
            }
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data berhasil difilter'
        ]);
    }


    /**
     * Handle filter requests for all laporan types.
     */
    public function filter(Request $request)
    {
        $type = $request->input('type');
        $filters = $request->except(['type', '_token']);

        switch ($type) {
            case 'harian-mekanik':
                return $this->filterHarianMekanik($request);
            case 'pemakaian-barang':
                return $this->filterPemakaianBarang($request);
            case 'arsip-wo':
                return $this->filterArsipWo($request);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    /**
     * Generate print/export for laporan.
     */
    public function print(Request $request, $type)
    {
        switch ($type) {
            case 'harian-mekanik':
                return $this->printHarianMekanik($request);
            case 'pemakaian-barang':
                return $this->printPemakaianBarang($request);
            case 'arsip-wo':
                return $this->printArsipWo($request);
            default:
                return response()->json(['error' => 'Invalid print type'], 400);
        }
    }

    /**
     * Print laporan harian mekanik.
     */
    public function printHarianMekanik(Request $request)
    {
        try {
            // Get data with same filters as the main method
            $query = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan']);

            // Apply filters if provided
            if ($request->has('tahun') && $request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
            }
            if ($request->has('bulan') && $request->bulan) {
                $query->whereMonth('tanggal', $request->bulan);
            }
            if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
                $tahun = $request->tahun ?: date('Y');
                $bulan = $request->bulan ?: date('m');
                
                // Calculate week range for real calendar weeks
                if (is_numeric($request->minggu)) {
                    $weekNumber = (int)$request->minggu;
                    $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                    
                    if ($weekRange) {
                        $query->whereBetween('tanggal', [
                            $weekRange['start'],
                            $weekRange['end']
                        ]);
                    } else {
                        // If week is beyond the month, return no data
                        $query->where('tanggal', '>', '9999-12-31');
                    }
                }
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // Generate period string for PDF
            $periode = 'Semua Data';
            if ($request->has('tahun') && $request->tahun) {
                $periode = 'Tahun ' . $request->tahun;
                if ($request->has('bulan') && $request->bulan) {
                    $bulanNames = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $periode = $bulanNames[$request->bulan] . ' ' . $request->tahun;
                }
            }

            $pdf = Pdf::loadView('pdf.laporan_harian_mekanik', compact('data', 'periode'));
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('laporan_harian_mekanik_' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mencetak laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print laporan pemakaian barang.
     */
    public function printPemakaianBarang(Request $request)
    {
        try {
            // Get data with same filters as the main method
            $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

            // Apply filters if provided
            if ($request->has('tahun') && $request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
            }
            if ($request->has('bulan') && $request->bulan) {
                $query->whereMonth('tanggal', $request->bulan);
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // Generate period string for PDF
            $periode = 'Semua Data';
            if ($request->has('tahun') && $request->tahun) {
                $periode = 'Tahun ' . $request->tahun;
                if ($request->has('bulan') && $request->bulan) {
                    $bulanNames = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $periode = $bulanNames[$request->bulan] . ' ' . $request->tahun;
                }
            }

            $pdf = Pdf::loadView('pdf.laporan_pemakaian_barang', compact('data', 'periode'));
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('laporan_pemakaian_barang_' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mencetak laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print laporan arsip WO.
     */
    public function printArsipWo(Request $request)
    {
        try {
            // Get data with same filters as the main method
            $query = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun']);

            // Apply filters if provided
            if ($request->has('kategori') && $request->kategori) {
                $query->where('id_verifikator', $request->kategori);
            }
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('no_surat_pengajuan', 'like', "%{$search}%")
                      ->orWhere('divisi_pengaju', 'like', "%{$search}%")
                      ->orWhere('ditujukan', 'like', "%{$search}%")
                      ->orWhere('uraian', 'like', "%{$search}%");
                });
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // Generate period string for PDF
            $periode = 'Semua Data';

            $pdf = Pdf::loadView('pdf.laporan_arsip_wo', compact('data', 'periode'));
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('laporan_arsip_wo_' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mencetak laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print laporan arsip WO by ID.
     */
    public function printArsipWoById(Request $request, $id)
    {
        // Get specific data by ID
        // Here you would generate PDF or Excel for specific work order
        return response()->json(['message' => "Laporan Arsip WO ID {$id} berhasil dicetak"]);
    }

    /**
     * Export laporan harian mekanik.
     */
    public function exportHarianMekanik(Request $request)
    {
        try {
            // Get data with same filters as the main method
            $query = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan']);

            // Apply filters if provided
            if ($request->has('tahun') && $request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
            }
            if ($request->has('bulan') && $request->bulan) {
                $query->whereMonth('tanggal', $request->bulan);
            }
            if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
                $tahun = $request->tahun ?: date('Y');
                $bulan = $request->bulan ?: date('m');
                
                // Calculate week range for real calendar weeks
                if (is_numeric($request->minggu)) {
                    $weekNumber = (int)$request->minggu;
                    $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                    
                    if ($weekRange) {
                        $query->whereBetween('tanggal', [
                            $weekRange['start'],
                            $weekRange['end']
                        ]);
                    } else {
                        // If week is beyond the month, return no data
                        $query->where('tanggal', '>', '9999-12-31');
                    }
                }
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // Generate period string
            $periode = 'Semua_Data';
            if ($request->has('tahun') && $request->tahun) {
                $periode = 'Tahun_' . $request->tahun;
                if ($request->has('bulan') && $request->bulan) {
                    $bulanNames = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $periode = $bulanNames[$request->bulan] . '_' . $request->tahun;
                }
            }
            
            $export = new LaporanHarianMekanikExport($data, $periode);
            return $export->download('laporan_harian_mekanik_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengekspor laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export laporan pemakaian barang.
     */
    public function exportPemakaianBarang(Request $request)
    {
        try {
            // Get data with same filters as the main method
            $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

            // Apply filters if provided
            if ($request->has('tahun') && $request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
            }
            if ($request->has('bulan') && $request->bulan) {
                $query->whereMonth('tanggal', $request->bulan);
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // Generate period string
            $periode = 'Semua_Data';
            if ($request->has('tahun') && $request->tahun) {
                $periode = 'Tahun_' . $request->tahun;
                if ($request->has('bulan') && $request->bulan) {
                    $bulanNames = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $periode = $bulanNames[$request->bulan] . '_' . $request->tahun;
                }
            }
            
            $export = new LaporanPemakaianBarangExport($data, $periode);
            return $export->download('laporan_pemakaian_barang_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengekspor laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export laporan arsip WO.
     */
    public function exportArsipWo(Request $request)
    {
        try {
            // Get data with same filters as the main method
            $query = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun']);

            // Apply filters if provided
            if ($request->has('kategori') && $request->kategori) {
                $query->where('id_verifikator', $request->kategori);
            }
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('no_surat_pengajuan', 'like', "%{$search}%")
                      ->orWhere('divisi_pengaju', 'like', "%{$search}%")
                      ->orWhere('ditujukan', 'like', "%{$search}%")
                      ->orWhere('uraian', 'like', "%{$search}%");
                });
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // LaporanArsipWoExport tidak ada, skip untuk sementara
            return response()->json(['error' => 'Export laporan arsip WO belum tersedia'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengekspor laporan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print laporan harian mekanik (private method for internal use).
     */
    private function printHarianMekanikInternal(Request $request)
    {
        // Get data
        $data = $this->harianMekanik($request);
        
        // Here you would generate PDF or Excel
        // For now, return success message
        return response()->json(['message' => 'Laporan Harian Mekanik berhasil dicetak']);
    }

    /**
     * Print laporan pemakaian barang (private method for internal use).
     */
    private function printPemakaianBarangInternal(Request $request)
    {
        // Get data
        $data = $this->pemakaianBarang($request);
        
        // Here you would generate PDF or Excel
        // For now, return success message
        return response()->json(['message' => 'Laporan Pemakaian Barang berhasil dicetak']);
    }

    /**
     * Print laporan arsip WO (private method for internal use).
     */
    private function printArsipWoInternal(Request $request)
    {
        // Get data
        $data = $this->arsipWo($request);
        
        // Here you would generate PDF or Excel
        // For now, return success message
        return response()->json(['message' => 'Laporan Arsip WO berhasil dicetak']);
    }

    /**
     * Get detail laporan harian mekanik by ID.
     */
    public function detailHarianMekanik($id)
    {
        try {
            $laporan = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan'])->findOrFail($id);
            
            return response()->json([
                'id_laporan_harian_mekanik' => $laporan->id_laporan_harian_mekanik,
                'tanggal' => $laporan->tanggal,
                'nama_unit' => $laporan->nama_unit,
                'keluhan_kerusakan' => $laporan->keluhan_kerusakan,
                'penyebab_kerusakan' => $laporan->penyebab_kerusakan,
                'tanggal_mulai' => $laporan->tanggal_mulai,
                'tanggal_selesai' => $laporan->tanggal_selesai,
                'tindakan_perbaikan' => $laporan->tindakan_perbaikan,
                'divisi' => $laporan->divisi ? $laporan->divisi->nama_divisi : '',
                'unit' => $laporan->unit ? $laporan->unit->nama_unit : '',
                'karyawan' => $laporan->akun && $laporan->akun->karyawan ? $laporan->akun->karyawan->nama_lengkap : ''
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Laporan tidak ditemukan'], 404);
        }
    }

    /**
     * Get detail laporan pemakaian barang by ID.
     */
    public function detailPemakaianBarang($id)
    {
        try {
            $laporan = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan'])->findOrFail($id);
            
            return response()->json([
                'id_laporan_pemakaian_barang' => $laporan->id_laporan_pemakaian_barang,
                'tanggal' => $laporan->tanggal,
                'nama_barang' => $laporan->nama_barang,
                'kode_unit' => $laporan->kode_unit,
                'jumlah' => $laporan->jumlah,
                'bentuk_satuan' => $laporan->bentuk_satuan,
                'harga_satuan' => $laporan->harga_satuan,
                'total_harga' => $laporan->total_harga,
                'keterangan' => $laporan->keterangan,
                'divisi' => $laporan->divisi ? $laporan->divisi->nama_divisi : '',
                'unit' => $laporan->unit ? $laporan->unit->nama_unit : '',
                'karyawan' => $laporan->akun && $laporan->akun->karyawan ? $laporan->akun->karyawan->nama_lengkap : ''
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Laporan tidak ditemukan'], 404);
        }
    }

    /**
     * Get detail work order by ID.
     */
    public function detailWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::with(['divisi', 'unit', 'akun'])->findOrFail($id);
            
            return response()->json([
                'id_surat_pengajuan' => $workOrder->id_surat_pengajuan,
                'no_surat_pengajuan' => $workOrder->no_surat_pengajuan,
                'divisi_pengaju' => $workOrder->divisi_pengaju,
                'ditujukan' => $workOrder->ditujukan,
                'tanggal' => $workOrder->tanggal,
                'unit' => $workOrder->unit, // This is a string field
                'uraian' => $workOrder->uraian,
                'dokumentasi' => $workOrder->dokumentasi,
                'status' => $workOrder->status ?? 'Menunggu',
                'id_verifikator' => $workOrder->id_verifikator,
                'divisi' => $workOrder->divisi ? $workOrder->divisi->nama_divisi : '',
                'unit_obj' => $workOrder->getRelation('unit') ? $workOrder->getRelation('unit')->nama_unit : '',
                'akun' => $workOrder->akun ? $workOrder->akun->email : ''
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Work order tidak ditemukan'], 404);
        }
    }

    /**
     * Display template for laporan harian mekanik.
     */
    public function templateHarianMekanik()
    {
        return view('export.laporan_harian');
    }
}
