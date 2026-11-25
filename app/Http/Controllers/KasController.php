<?php

namespace App\Http\Controllers;

use App\Exports\KasExport;
use App\Models\Kas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class KasController extends Controller
{
    /**
     * Helper untuk membangun query dengan filter, search, dan sorting yang konsisten.
     */
    protected function getFilteredQuery(Request $request)
    {
        $query = Kas::with('user');

        // Search berdasarkan keterangan atau nama pencatat
        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // Filter berdasarkan jenis (Pemasukan / Pengeluaran)
        $jenis = $request->input('jenis');
        if ($jenis && $jenis !== 'all') {
            $query->where('jenis', $jenis);
        }

        // Filter berdasarkan rentang tanggal atau tanggal tunggal
        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');
        if ($dariTanggal && $sampaiTanggal) {
            $query->whereBetween('tanggal', [$dariTanggal, $sampaiTanggal]);
        } elseif ($dariTanggal) {
            $query->whereDate('tanggal', '>=', $dariTanggal);
        } elseif ($sampaiTanggal) {
            $query->whereDate('tanggal', '<=', $sampaiTanggal);
        } elseif ($tanggal = $request->input('tanggal')) {
            if ($tanggal !== 'all') {
                $query->whereDate('tanggal', $tanggal);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'tanggal');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSort = ['tanggal', 'nominal', 'user', 'keterangan'];
        if (!in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'tanggal';
        }

        if ($sortBy === 'user') {
            $query->join('users', 'kas.id_users', '=', 'users.id_users')
                  ->orderBy('users.nama', $sortOrder)
                  ->select('kas.*');
        } else {
            $query->orderBy($sortBy, $sortOrder)->orderBy('id_kas', 'desc');
        }

        return $query;
    }

    /**
     * Menampilkan daftar keuangan kas.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Tampilan Khusus Siswa (Read-only)
        if ($user->isUser()) {
            $query = Kas::with('user')->orderBy('tanggal', 'desc')->orderBy('id_kas', 'desc');

            if ($jenis = $request->input('jenis')) {
                if ($jenis !== 'all') {
                    $query->where('jenis', $jenis);
                }
            }

            $kasList = $query->paginate(10)->withQueryString();
            $totalPemasukan = Kas::where('jenis', 'Pemasukan')->sum('nominal');
            $totalPengeluaran = Kas::where('jenis', 'Pengeluaran')->sum('nominal');
            $saldoAkhir = $totalPemasukan - $totalPengeluaran;

            return view('kas.index_siswa', compact('kasList', 'totalPemasukan', 'totalPengeluaran', 'saldoAkhir'));
        }

        // 2. Tampilan Admin & Guru
        $query = $this->getFilteredQuery($request);
        $kasList = $query->paginate(10)->withQueryString();

        $totalPemasukan = Kas::where('jenis', 'Pemasukan')->sum('nominal');
        $totalPengeluaran = Kas::where('jenis', 'Pengeluaran')->sum('nominal');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        $filters = [
            'q' => $request->input('q', ''),
            'jenis' => $request->input('jenis', 'all'),
            'dari_tanggal' => $request->input('dari_tanggal', ''),
            'sampai_tanggal' => $request->input('sampai_tanggal', ''),
            'sort_by' => $request->input('sort_by', 'tanggal'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        return view('kas.index', compact('kasList', 'totalPemasukan', 'totalPengeluaran', 'saldoAkhir', 'filters'));
    }

    /**
     * Form tambah pencatatan transaksi kas baru.
     */
    public function create()
    {
        return view('kas.create');
    }

    /**
     * Simpan transaksi kas baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:Pemasukan,Pengeluaran',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'required|string|max:1000',
        ], [
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'jenis.required' => 'Jenis transaksi wajib dipilih.',
            'jenis.in' => 'Jenis transaksi harus berupa Pemasukan atau Pengeluaran.',
            'nominal.required' => 'Nominal transaksi wajib diisi.',
            'nominal.numeric' => 'Nominal transaksi harus berupa angka yang valid.',
            'nominal.min' => 'Nominal transaksi minimal Rp 1.',
            'keterangan.required' => 'Keterangan transaksi wajib diisi.',
        ]);

        Kas::create([
            'id_users' => Auth::id(),
            'tanggal' => $request->tanggal,
            'jenis' => $request->jenis,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('kas.index')
            ->with('success', 'Transaksi kas berhasil dicatat.');
    }

    /**
     * Form edit transaksi kas.
     */
    public function edit(Kas $ka)
    {
        $kas = $ka;
        return view('kas.edit', compact('kas'));
    }

    /**
     * Update transaksi kas.
     */
    public function update(Request $request, Kas $ka)
    {
        $kas = $ka;
        $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:Pemasukan,Pengeluaran',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'required|string|max:1000',
        ], [
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'jenis.required' => 'Jenis transaksi wajib dipilih.',
            'jenis.in' => 'Jenis transaksi harus berupa Pemasukan atau Pengeluaran.',
            'nominal.required' => 'Nominal transaksi wajib diisi.',
            'nominal.numeric' => 'Nominal transaksi harus berupa angka yang valid.',
            'nominal.min' => 'Nominal transaksi minimal Rp 1.',
            'keterangan.required' => 'Keterangan transaksi wajib diisi.',
        ]);

        $kas->update([
            'tanggal' => $request->tanggal,
            'jenis' => $request->jenis,
            'nominal' => $request->nominal,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('kas.index')
            ->with('success', 'Transaksi kas berhasil diperbarui.');
    }

    /**
     * Hapus transaksi kas.
     */
    public function destroy(Kas $ka)
    {
        $kas = $ka;
        $kas->delete();

        return redirect()->route('kas.index')
            ->with('success', 'Transaksi kas berhasil dihapus.');
    }

    /**
     * Export laporan kas ke Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $items = $query->get();

        return Excel::download(new KasExport($items), 'laporan-kas.xlsx');
    }

    /**
     * Export laporan kas ke PDF (.pdf).
     */
    public function exportPdf(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $items = $query->get();

        $totalPemasukan = $items->where('jenis', 'Pemasukan')->sum('nominal');
        $totalPengeluaran = $items->where('jenis', 'Pengeluaran')->sum('nominal');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        $filters = [
            'jenis' => $request->input('jenis', 'all'),
            'dari_tanggal' => $request->input('dari_tanggal'),
            'sampai_tanggal' => $request->input('sampai_tanggal'),
        ];

        $pdf = Pdf::loadView('exports.kas_pdf', compact('items', 'totalPemasukan', 'totalPengeluaran', 'saldoAkhir', 'filters'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('laporan-kas.pdf');
    }
}
