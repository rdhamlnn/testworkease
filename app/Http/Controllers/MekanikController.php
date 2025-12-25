<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\LaporanHarianMekanik;
use App\Models\LaporanPemakaianBarang;
use App\Models\Divisi;
use App\Models\Unit;
use App\Models\Akun;
use Carbon\Carbon;

class MekanikController extends Controller
{
    use \App\Traits\UserProfileActions, \App\Traits\ReportHelper;


    /**
     * Get the view name for profile.
     */
    protected function getProfileView()
    {
        return 'mekanik.profile';
    }


    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        // Get statistics for dashboard (real data from database)
        $totalLaporanHarian = LaporanHarianMekanik::count();
        $totalLaporanBarang = LaporanPemakaianBarang::count();
        
        // Laporan bulan ini (combined from both tables)
        $laporanBulanIni = LaporanHarianMekanik::whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->count() 
            + LaporanPemakaianBarang::whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->count();

        // ========== DATA CHART REAL DARI DATABASE ==========
        
        // 1. Trend Laporan (6 bulan terakhir) - Combined from both tables using UNION
        $laporanHarianTrend = LaporanHarianMekanik::select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('count(*) as total')
            )
            ->where('tanggal', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month');
            
        $laporanBarangTrend = LaporanPemakaianBarang::select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('count(*) as total')
            )
            ->where('tanggal', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month');
        
        // Combine and aggregate
        $monthlyLaporanTrend = DB::table(DB::raw("({$laporanHarianTrend->toSql()} UNION ALL {$laporanBarangTrend->toSql()}) as combined"))
            ->mergeBindings($laporanHarianTrend->getQuery())
            ->mergeBindings($laporanBarangTrend->getQuery())
            ->select(
                'month',
                'year',
                DB::raw('SUM(total) as total')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // 2. Pemakaian Barang per Unit (real data)
        $materialUsagePerUnit = LaporanPemakaianBarang::select('kode_unit', DB::raw('SUM(total_harga) as total'))
            ->whereNotNull('kode_unit')
            ->groupBy('kode_unit')
            ->get()
            ->pluck('total', 'kode_unit')
            ->toArray();

        // 3. Aktivitas Terbaru (real data from laporan harian mekanik)
        $recentActivities = LaporanHarianMekanik::join('akun', 'laporan_harian_mekanik.id_akun', '=', 'akun.id_akun')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->select('laporan_harian_mekanik.*', 'karyawan.nama_lengkap')
            ->orderBy('laporan_harian_mekanik.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('mekanik.dashboard', compact(
            'totalLaporanHarian', 
            'totalLaporanBarang',
            'laporanBulanIni',
            'monthlyLaporanTrend',
            'materialUsagePerUnit',
            'recentActivities'
        ));
    }

    /**
     * Display laporan harian mekanik page.
     */
    public function laporanHarianMekanik(Request $request)
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

        // Get unit options for dropdown
        $unitOptions = Unit::all();
        $divisiOptions = Divisi::all();

        return view('mekanik.laporan_harian_mekanik', compact('data', 'tahunOptions', 'bulanOptions', 'unitOptions', 'divisiOptions'));
    }

    /**
     * Display laporan pemakaian barang page.
     */
    public function laporanPemakaianBarang(Request $request)
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

        // Get unit options for dropdown
        $unitOptions = Unit::all();
        $divisiOptions = Divisi::all();

        return view('mekanik.laporan_pemakaian_barang', compact('data', 'tahunOptions', 'bulanOptions', 'unitOptions', 'divisiOptions'));
    }

    /**
     * Filter laporan harian mekanik.
     */
    public function filterLaporanHarianMekanik(Request $request)
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
    public function filterLaporanPemakaianBarang(Request $request)
    {
        $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun']);

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
     * Store laporan harian mekanik.
     */
    public function storeLaporanHarianMekanik(Request $request)
    {
        try {
            $request->validate([
                'tanggal' => 'required|date',
                'nama_unit' => 'required|string',
                'keluhan_kerusakan' => 'required|string',
                'penyebab_kerusakan' => 'required|string',
                'tanggal_mulai' => 'required|date',
                'tanggal_selesai' => 'required|date',
                'tindakan_perbaikan' => 'required|string',
            ]);

            // Handle unit input - sederhana untuk form yang ada
            $unitId = 1; // Default unit ID

            // Simpan data ke database
            LaporanHarianMekanik::create([
                'tanggal' => $request->tanggal,
                'nama_unit' => $request->nama_unit,
                'keluhan_kerusakan' => $request->keluhan_kerusakan,
                'penyebab_kerusakan' => $request->penyebab_kerusakan,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'tindakan_perbaikan' => $request->tindakan_perbaikan,
                'id_divisi' => session('user_divisi', 1), // Divisi dari session user
                'id_unit' => $unitId,
                'id_akun' => session('user_id', 1), // ID akun dari session
            ]);

            return redirect()->back()->with('success', 'Data berhasil ditambahkan')->with('from_crud', true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan laporan harian mekanik: ' . $e->getMessage());
        }
    }

    /**
     * Show laporan harian mekanik.
     */
    public function showLaporanHarianMekanik($id)
    {
        $laporan = LaporanHarianMekanik::with(['divisi', 'unit', 'akun'])->findOrFail($id);
        return response()->json($laporan);
    }

    /**
     * Store laporan pemakaian barang.
     */
    public function storeLaporanPemakaianBarang(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kode_unit' => 'required|string',
            'nama_barang' => 'required|string',
            'jumlah' => 'required|numeric|min:1',
            'bentuk_satuan' => 'required|string',
            'harga_satuan' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'unit_option' => 'required|in:existing,new',
            'existing_unit_id' => 'nullable|exists:unit,id_unit',
            'new_unit_name' => 'nullable|string|max:255',
            'new_unit_code' => 'nullable|string|max:50',
        ]);

        // Handle unit input
        $unitId = 1; // Default
        if ($request->unit_option === 'existing' && $request->existing_unit_id) {
            $unitId = $request->existing_unit_id;
        } elseif ($request->unit_option === 'new' && $request->new_unit_name) {
            // Create new unit
            $newUnit = Unit::create([
                'nama_unit' => $request->new_unit_name,
                'kode_unit' => $request->new_unit_code ?? 'NEW-' . time(),
                'no_polisi' => null,
                'jenis_unit' => 'Manual',
                'merk_unit' => null,
                'tahun_pembuatan' => null,
            ]);
            $unitId = $newUnit->id_unit;
        }

        // Simpan data ke database
        LaporanPemakaianBarang::create([
            'tanggal' => $request->tanggal,
            'kode_unit' => $request->kode_unit,
            'nama_barang' => $request->nama_barang,
            'jumlah' => $request->jumlah,
            'bentuk_satuan' => $request->bentuk_satuan,
            'harga_satuan' => $request->harga_satuan,
            'total_harga' => $request->total_harga,
            'keterangan' => $request->keterangan,
            'id_divisi' => session('user_divisi', 1), // Divisi dari session user
            'id_unit' => $unitId,
            'id_akun' => session('user_id', 1), // ID akun dari session
        ]);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan')->with('from_crud', true);
    }

    /**
     * Show laporan pemakaian barang.
     */
    public function showLaporanPemakaianBarang($id)
    {
        $laporan = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun'])->findOrFail($id);
        return response()->json($laporan);
    }









    /**
     * API: Get laporan count.
     */
    public function getLaporanCount()
    {
        $counts = [
            'harian_mekanik' => LaporanHarianMekanik::count(),
            'pemakaian_barang' => LaporanPemakaianBarang::count(),
        ];

        return response()->json($counts);
    }

    /**
     * API: Get dashboard stats.
     */
    public function getDashboardStats()
    {
        $stats = [
            'total_laporan_harian' => LaporanHarianMekanik::count(),
            'total_laporan_barang' => LaporanPemakaianBarang::count(),
        ];

        return response()->json($stats);
    }

    /**
     * API: Get recent activities.
     */
    public function getRecentActivities()
    {
        $activities = collect([]); // Implementation here
        
        return response()->json($activities);
    }

    /**
     * Test method for debugging.
     */
    public function test()
    {
        return response()->json([
            'message' => 'MekanikController is working!',
            'timestamp' => now(),
            'session_data' => [
                'user_id' => session('user_id'),
                'user_peran' => session('user_peran'),
                'user_email' => session('user_email'),
            ]
        ]);
    }

}
