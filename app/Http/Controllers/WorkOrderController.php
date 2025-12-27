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
use App\Models\DaftarBarang;
use Barryvdh\DomPDF\Facade\Pdf;


class WorkOrderController extends Controller
{
    use \App\Traits\WorkOrderActions;

    /**
     * Get redirect route for work order.
     */
    protected function getWorkOrderRedirectRoute()
    {
        return route('admin.work-order');
    }

    /**
     * Get redirect route for work order masukan.
     */
    protected function getDaftarPengajuanRedirectRoute()
    {
        return route('admin.daftar-pengajuan-work-order', ['from' => 'crud']);
    }

    /**
     * Display a listing of work orders.
     * Admin: Menampilkan semua work order dari seluruh divisi
     * Non-Admin: Menampilkan hanya work order yang dibuat oleh divisi pengaju
     */
    public function index()
    {
        $userPeran = Session::get('user_peran');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Administrator';

        if ($userPeran == 1) { // Admin
            $workOrders = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun', 'jenisWorkOrder'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $workOrders = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun', 'jenisWorkOrder'])
                ->fromDivisi($userDivisiNama)
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
                'unit' => $item->getAttributes()['unit'] ?? null,
                'unit_code' => $item->unit->nama_unit ?? $item->getAttributes()['unit'],
                'uraian' => $item->uraian,
                'dokumentasi' => $item->dokumentasi,
                'status' => $item->verifikator->nama_status ?? 'Menunggu',
                'verifikator' => $item->verifikator,
                'jenisWorkOrder' => $item->jenisWorkOrder,
                'divisi_nama' => $item->divisi->nama_divisi ?? '',
                'unit_nama' => $item->unit->nama_unit ?? '',
                'akun_email' => $item->akun->email ?? ''
            ];
        });

        $divisi = Divisi::where('nama_divisi', '!=', 'Administrator')->get();
        $unit = Unit::all();
        $statusVerifikator = StatusVerifikator::all();
        $karyawan = DB::table('karyawan')->get();
        $unitOptions = Unit::all();
        $jenisWorkOrder = JenisWorkOrder::all();
        $nextWorkOrderNumber = $this->generateWorkOrderNumber('ADM');
        $daftarBarang = DaftarBarang::all();

        return view('admin.work_order', compact('workOrders', 'divisi', 'unit', 'statusVerifikator', 'nextWorkOrderNumber', 'karyawan', 'unitOptions', 'jenisWorkOrder', 'daftarBarang'));
    }

    /**
     * Display daftar pengajuan work orders (WO yang diterima dari divisi lain).
     */
    public function daftarPengajuan()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Administrator';

        $submissionWorkOrders = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun', 'jenisWorkOrder'])
            ->toDivisi($userDivisiNama)
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
                    'unit_code' => $item->unit->nama_unit ?? $item->unit,
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
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Administrator';

        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator'])
            ->dibuatAtauDiterima($userDivisiNama)
            ->byVerifikator([2, 3])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.riwayat_work_order', compact('workOrders'));
    }

    /**
     * Store a newly created work order.
     */
    public function store(Request $request)
    {
        if (Session::get('user_peran') == 1) {
            return redirect()->route('admin.work-order')->with('error', 'Admin tidak dapat membuat work order.');
        }

        $request->validate([
            'ditujukan' => 'required|string',
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required|string',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $dokumentasiPath = $this->handleUpload($request->file('dokumentasi'), 'work-orders');

            SuratPengajuan::create([
                'no_surat_pengajuan' => $this->generateWorkOrderNumber('ADM'),
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi'),
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_divisi' => Session::get('user_divisi'),
                'id_peran' => Session::get('user_peran'),
                'id_verifikator' => 1,
                'id_akun' => Session::get('user_id'),
                'id_unit' => Unit::where('nama_unit', $request->unit)->value('id_unit') ?: 1,
            ]);

            return redirect()->route('admin.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.work-order')->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function getData($id)
    {
        return $this->showWorkOrder($id);
    }

    public function update(Request $request, $id)
    {
        if (Session::get('user_peran') == 1) {
            return redirect()->route('admin.work-order')->with('error', 'Admin tidak dapat mengedit work order.');
        }

        $request->validate([
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required|string',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                return redirect()->back()->with('error', 'Work Order sudah diproses.');
            }

            $dokumentasiPath = $this->handleUpload($request->file('dokumentasi'), 'work-orders', $workOrder->dokumentasi);

            $workOrder->update([
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_unit' => Unit::where('nama_unit', $request->unit)->value('id_unit') ?: 1,
            ]);

            return redirect()->route('admin.work-order', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('admin.work-order')->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (Session::get('user_peran') == 1) {
            return response()->json(['success' => false, 'message' => 'Admin tidak dapat menghapus work order.'], 403);
        }
        return $this->hapusWorkOrder($id);
    }

    public function approve(Request $request, $id)
    {
        return $this->approveWorkOrder($id);
    }

    public function reject(Request $request, $id)
    {
        return $this->rejectWorkOrder($id);
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
            'unit',
            'permintaanBarang.daftarBarang'
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
        $filename = "WorkOrder_{{$cleanNo}}.pdf";

        return $pdf->stream($filename);
    }
}
