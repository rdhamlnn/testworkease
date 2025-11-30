<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Models\SuratPengajuan;
use App\Models\Divisi;
use App\Models\Unit;
use App\Models\StatusVerifikator;
use App\Models\Akun;
use App\Models\JenisWorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;


class WorkOrderController extends Controller
{
    /**
     * Display a listing of work orders.
     * Admin: Menampilkan semua work order dari seluruh divisi
     * Non-Admin: Menampilkan hanya work order yang dibuat oleh divisi pengaju
     */
    public function index()
    {
        $userPeran = Session::get('user_peran');
        $userDivisi = Session::get('user_divisi');

        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Administrator';

        // Jika admin, tampilkan semua work order dari seluruh divisi
        // Jika bukan admin, tampilkan hanya work order yang dibuat oleh divisi pengaju
        if ($userPeran == 1) { // Admin
            $workOrders = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun', 'jenisWorkOrder'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // WO yang dibuat oleh divisi pengaju (akun yang login)
            $workOrders = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun', 'jenisWorkOrder'])
                ->where('divisi_pengaju', $userDivisiNama)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $workOrders = $workOrders->map(function ($item) {
            return (object) [
                'id' => $item->id_surat_pengajuan,
                'no_work_order' => $item->no_surat_pengajuan,
                'divisi_pengaju' => $item->divisi_pengaju,
                'ditujukan' => $item->ditujukan,
                'tanggal' => $item->tanggal,
                'unit_code' => is_object($item->unit) ? $item->unit->nama_unit : $item->unit,
                'uraian' => $item->uraian,
                'dokumentasi' => $item->dokumentasi,
                'status' => $item->verifikator->nama_status ?? 'Menunggu',
                'jenisWorkOrder' => $item->jenisWorkOrder,
                'divisi_nama' => $item->divisi->nama_divisi ?? '',
                'unit_nama' => $item->unit->nama_unit ?? '',
                'akun_email' => $item->akun->email ?? ''
            ];
        });

        // Ambil data untuk dropdown (exclude Administrator)
        $divisi = Divisi::where('nama_divisi', '!=', 'Administrator')->get();
        $unit = Unit::all();
        $statusVerifikator = StatusVerifikator::all();
        $karyawan = DB::table('karyawan')->get(); // Untuk dropdown ditujukan
        $unitOptions = Unit::all();
        $jenisWorkOrder = JenisWorkOrder::all();

        $nextWorkOrderNumber = $this->generateWorkOrderNumber();

        return view('admin.work_order', compact('workOrders', 'divisi', 'unit', 'statusVerifikator', 'nextWorkOrderNumber', 'karyawan', 'unitOptions', 'jenisWorkOrder'));
    }

    /**
     * Display daftar pengajuan work orders (WO yang diterima dari divisi lain).
     */
    public function daftarPengajuan()
    {
        $userDivisi = Session::get('user_divisi');

        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Administrator';

        // WO yang diterima oleh divisi user yang login (dari divisi lain)
        $submissionWorkOrders = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun', 'jenisWorkOrder'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->id_surat_pengajuan,
                    'no_work_order' => $item->no_surat_pengajuan,
                    'divisi_pengaju' => $item->divisi_pengaju,
                    'ditujukan' => $item->ditujukan,
                    'tanggal' => $item->tanggal,
                    'unit_code' => is_object($item->unit) ? $item->unit->nama_unit : $item->unit,
                    'uraian' => $item->uraian,
                    'dokumentasi' => $item->dokumentasi,
                    'status' => $item->verifikator->nama_status ?? 'Menunggu',
                    'id_verifikator' => $item->id_verifikator,
                    'divisi_nama' => $item->divisi->nama_divisi ?? '',
                    'unit_nama' => $item->unit->nama_unit ?? '',
                    'akun_email' => $item->akun->email ?? '',
                    'created_at' => $item->created_at
                ];
            });

        return view('admin.daftar_pengajuan_work_order', compact('submissionWorkOrders'));
    }

    /**
     * Display riwayat work order page (WO yang sudah selesai).
     */
    public function riwayatWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');

        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Administrator';

        // WO yang sudah selesai (status Disetujui - id_verifikator = 2) atau Ditolak (id_verifikator = 3)
        // Gabungkan WO yang dibuat dan diterima oleh divisi user yang login
        $workOrdersDibuat = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator'])
            ->where('divisi_pengaju', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Status Disetujui (2) dan Ditolak (3)
            ->get();

        $workOrdersDiterima = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Status Disetujui (2) dan Ditolak (3)
            ->get();

        $workOrders = $workOrdersDibuat->merge($workOrdersDiterima)->sortByDesc('created_at');

        return view('admin.riwayat_work_order', compact('workOrders'));
    }

    /**
     * Store a newly created work order.
     */
    public function store(Request $request)
    {
        // Validasi: Admin tidak boleh membuat work order
        $userPeran = Session::get('user_peran');
        if ($userPeran == 1) { // 1 = Admin
            return redirect()->route('admin.work-order')
                ->with('error', 'Admin tidak dapat membuat work order. Admin hanya dapat memantau dan mengelola data.');
        }

        // Validasi: Divisi lain tidak boleh mengajukan ke Admin
        $adminDivisi = DB::table('divisi')->where('nama_divisi', 'Administrator')->first();
        if ($adminDivisi && $request->ditujukan === 'Administrator') {
            return redirect()->back()
                ->with('error', 'Tidak dapat mengajukan work order ke Administrator. Administrator hanya berfungsi untuk memantau dan mengelola data.');
        }

        $request->validate([
            'no_surat_pengajuan' => 'required|string|max:255',
            'ditujukan' => 'required|string|max:255',
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'divisi_pengaju' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'id_divisi' => 'required|exists:divisi,id_divisi',
            'id_unit' => 'required|exists:unit,id_unit',
            'id_verifikator' => 'required|exists:status_verifikator,id_verifikator',
            'id_akun' => 'required|exists:akun,id_akun'
        ]);

        try {
            // Handle file upload
            $dokumentasiPath = null;
            if ($request->hasFile('dokumentasi')) {
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            // Simpan ke database
            SuratPengajuan::create([
                'no_surat_pengajuan' => $request->no_surat_pengajuan,
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => $request->divisi_pengaju,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_divisi' => $request->id_divisi,
                'id_peran' => 1, // Default admin role
                'id_verifikator' => $request->id_verifikator,
                'id_akun' => $request->id_akun,
                'id_unit' => $request->id_unit
            ]);

            return redirect()->route('admin.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.work-order')->with('error', 'Gagal membuat work order: ' . $e->getMessage());
        }
    }

    /**
     * Get work order data for AJAX requests.
     */
    public function getData($id)
    {
        try {
            $workOrder = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun'])
                ->findOrFail($id);

            $data = (object) [
                'id' => $workOrder->id_surat_pengajuan,
                'no_work_order' => $workOrder->no_surat_pengajuan,
                'divisi_pengaju' => $workOrder->divisi_pengaju,
                'ditujukan' => $workOrder->ditujukan,
                'tanggal' => $workOrder->tanggal,
                'unit_code' => $workOrder->unit->nama_unit ?? $workOrder->unit,
                'uraian' => $workOrder->uraian,
                'dokumentasi' => $workOrder->dokumentasi,
                'status' => $workOrder->verifikator->status ?? 'Menunggu',
                'id_divisi' => $workOrder->id_divisi,
                'id_unit' => $workOrder->id_unit,
                'id_verifikator' => $workOrder->id_verifikator,
                'id_akun' => $workOrder->id_akun
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }
    }

    /**
     * Update the specified work order.
     */
    public function update(Request $request, $id)
    {
        // Validasi: Admin tidak boleh mengedit work order
        $userPeran = Session::get('user_peran');
        if ($userPeran == 1) { // 1 = Admin
            return redirect()->route('admin.work-order')
                ->with('error', 'Admin tidak dapat mengedit work order. Admin hanya dapat melihat detail.');
        }

        $request->validate([
            'no_surat_pengajuan' => 'required|string|max:255',
            'ditujukan' => 'required|string|max:255',
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'divisi_pengaju' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'id_divisi' => 'required|exists:divisi,id_divisi',
            'id_unit' => 'required|exists:unit,id_unit',
            'id_verifikator' => 'required|exists:status_verifikator,id_verifikator',
            'id_akun' => 'required|exists:akun,id_akun'
        ]);

        try {
            $workOrder = SuratPengajuan::findOrFail($id);

            // Validasi: tidak bisa edit jika sudah disetujui atau ditolak
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                return redirect()->back()
                    ->with('error', 'Work Order tidak dapat diedit karena sudah disetujui atau ditolak.');
            }

            // Handle file upload if new file is provided
            $dokumentasiPath = $workOrder->dokumentasi;
            if ($request->hasFile('dokumentasi')) {
                // Delete old file if exists
                if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                    Storage::disk('public')->delete($workOrder->dokumentasi);
                }
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            // Update database
            $workOrder->update([
                'no_surat_pengajuan' => $request->no_surat_pengajuan,
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => $request->divisi_pengaju,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_divisi' => $request->id_divisi,
                'id_verifikator' => $request->id_verifikator,
                'id_akun' => $request->id_akun,
                'id_unit' => $request->id_unit
            ]);

            return redirect()->route('admin.work-order', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.work-order')->with('error', 'Gagal mengupdate work order: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified work order.
     */
    public function destroy($id)
    {
        // Validasi: Admin tidak boleh menghapus work order
        $userPeran = Session::get('user_peran');
        if ($userPeran == 1) { // 1 = Admin
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak dapat menghapus work order. Admin hanya dapat melihat detail.'
            ], 403);
        }

        try {
            $workOrder = SuratPengajuan::findOrFail($id);

            // Validasi: tidak bisa hapus jika sudah disetujui atau ditolak
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work Order tidak dapat dihapus karena sudah disetujui atau ditolak.'
                ], 403);
            }

            // Delete file if exists
            if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                Storage::disk('public')->delete($workOrder->dokumentasi);
            }

            // Delete from database
            $workOrder->delete();

            // Simpan success message di session sebelum return JSON
            session()->flash('success', 'Data berhasil dihapus!');
            session()->flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => route('admin.work-order')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve work order.
     */
    public function approve(Request $request, $id)
    {
        try {
            $workOrder = SuratPengajuan::findOrFail($id);

            // Update status to approved (assuming status_verifikator with id 2 is "Disetujui")
            $workOrder->update(['id_verifikator' => 2]);

            return response()->json(['success' => true, 'message' => 'Work Order berhasil disetujui!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyetujui work order: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject work order.
     */
    public function reject(Request $request, $id)
    {
        try {
            $workOrder = SuratPengajuan::findOrFail($id);

            // Update status to rejected (assuming status_verifikator with id 3 is "Ditolak")
            $workOrder->update(['id_verifikator' => 3]);

            return response()->json(['success' => true, 'message' => 'Work Order berhasil ditolak!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menolak work order: ' . $e->getMessage()]);
        }
    }

    /**
     * Print work order.
     */
    public function cetakPDF($id)
    {
        $wo = SuratPengajuan::with([
            'jenisWorkOrder',
            'verifikator',
            'akun.karyawan',
            'akun.divisi',
            'divisiPengaju',
            'unit'
        ])->findOrFail($id);

        // Akun pembuat WO
        $dibuatOleh = $wo->akun;

        // Akun divisi tujuan berdasarkan nama divisi di kolom 'ditujukan'
        $diketahuiOleh = Akun::with(['karyawan', 'divisi'])
            ->whereHas('divisi', function ($q) use ($wo) {
                $q->where('nama_divisi', $wo->ditujukan);
            })
            ->first();

        // Status badge
        $wo->status_text = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';

        $pdf = Pdf::loadView('admin.cetak_work_order_pdf', [
            'wo' => $wo,
            'dibuatOleh' => $dibuatOleh,
            'diketahuiOleh' => $diketahuiOleh,
        ])->setPaper('A4', 'portrait');

        $cleanNo = str_replace(['/', '\\'], '-', $wo->no_surat_pengajuan);
        $filename = "WorkOrder_{$cleanNo}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Generate work order number.
     */
    private function generateWorkOrderNumber()
    {
        $year = date('Y');
        $month = date('m');

        // Get the last work order number for this year
        $lastWorkOrder = SuratPengajuan::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastWorkOrder) {
            // Extract sequence number from last work order
            $lastNumber = explode('/', $lastWorkOrder->no_surat_pengajuan)[0];
            $sequence = str_pad((int) $lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $sequence = '01';
        }

        return "{$sequence}/ADM/KCE/{$year}";
    }
}
