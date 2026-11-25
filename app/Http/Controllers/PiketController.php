<?php

namespace App\Http\Controllers;

use App\Exports\PiketExport;
use App\Models\Anggota;
use App\Models\Piket;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class PiketController extends Controller
{
    /**
     * Menampilkan daftar jadwal piket.
     * Siswa: hanya melihat jadwal miliknya sendiri.
     * Admin & Guru: melihat seluruh jadwal dengan fitur pencarian, filter, dan sorting.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Tampilan Khusus Siswa
        if ($user->isUser()) {
            $anggota = $user->anggota;
            $piketList = collect();
            $stats = [
                'total' => 0,
                'selesai' => 0,
                'belum' => 0,
            ];

            if ($anggota) {
                $query = Piket::with(['anggota.user'])
                    ->where('id_anggota', $anggota->id_anggota);

                if ($status = $request->input('status')) {
                    if ($status !== 'all') {
                        $query->where('status', $status);
                    }
                }

                if ($tanggal = $request->input('tanggal')) {
                    if ($tanggal !== 'all') {
                        $query->whereDate('tanggal', $tanggal);
                    }
                }

                $piketList = $query->orderBy('tanggal', 'desc')->get();
                $stats['total'] = $piketList->count();
                $stats['selesai'] = $piketList->where('status', 'Selesai')->count();
                $stats['belum'] = $piketList->where('status', 'Belum')->count();
            }

            return view('piket.index_siswa', compact('piketList', 'stats'));
        }

        // 2. Tampilan Admin & Guru (Mendukung Search, Filter, Sorting, Eager Loading)
        $query = Piket::with(['anggota.user']);

        // Search berdasarkan nama siswa
        if ($search = $request->input('q')) {
            $query->whereHas('anggota.user', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan tanggal
        $tanggalFilter = $request->input('tanggal');
        if ($tanggalFilter && $tanggalFilter !== 'all') {
            $query->whereDate('tanggal', $tanggalFilter);
        }

        // Filter berdasarkan status
        $statusFilter = $request->input('status');
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        // Sorting: berdasarkan tanggal atau nama siswa
        $sortBy = $request->input('sort_by', 'tanggal');
        $sortOrder = strtolower($request->input('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'nama') {
            $query->join('anggota', 'piket.id_anggota', '=', 'anggota.id_anggota')
                  ->join('users', 'anggota.id_users', '=', 'users.id_users')
                  ->orderBy('users.nama', $sortOrder)
                  ->select('piket.*');
        } else {
            $query->orderBy('tanggal', $sortOrder)->orderBy('id_piket', 'desc');
        }

        $piketList = $query->get();
        $anggotaList = Anggota::with('user')->orderBy('id_anggota')->get();

        return view('piket.index', compact('piketList', 'anggotaList', 'tanggalFilter', 'statusFilter', 'sortBy', 'sortOrder'));
    }

    /**
     * Form tambah jadwal piket baru.
     * Akses: Admin & Guru.
     */
    public function create()
    {
        $anggotaList = Anggota::with('user')->orderBy('id_anggota')->get();
        return view('piket.create', compact('anggotaList'));
    }

    /**
     * Simpan jadwal piket baru.
     * Akses: Admin & Guru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'tanggal' => 'required|date',
            'tugas' => 'required|string|max:100',
            'status' => 'required|in:Selesai,Belum',
        ], [
            'id_anggota.required' => 'Pilih petugas piket terlebih dahulu.',
            'id_anggota.exists' => 'Siswa yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal piket wajib diisi.',
            'tugas.required' => 'Tugas kebersihan wajib diisi.',
            'status.required' => 'Status piket wajib dipilih.',
            'status.in' => 'Status piket harus Selesai atau Belum.',
        ]);

        Piket::create([
            'id_anggota' => $request->id_anggota,
            'tanggal' => $request->tanggal,
            'tugas' => $request->tugas,
            'status' => $request->status,
        ]);

        return redirect()->route('piket.index')
            ->with('success', 'Jadwal piket berhasil ditambahkan.');
    }

    /**
     * Form ubah jadwal piket.
     * Akses: Admin & Guru.
     */
    public function edit(Piket $piket)
    {
        $piket->load('anggota.user');
        $anggotaList = Anggota::with('user')->orderBy('id_anggota')->get();

        return view('piket.edit', compact('piket', 'anggotaList'));
    }

    /**
     * Update jadwal piket.
     * Akses: Admin & Guru.
     */
    public function update(Request $request, Piket $piket)
    {
        $request->validate([
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'tanggal' => 'required|date',
            'tugas' => 'required|string|max:100',
            'status' => 'required|in:Selesai,Belum',
        ], [
            'id_anggota.required' => 'Pilih petugas piket terlebih dahulu.',
            'tanggal.required' => 'Tanggal piket wajib diisi.',
            'tugas.required' => 'Tugas kebersihan wajib diisi.',
            'status.required' => 'Status piket wajib dipilih.',
            'status.in' => 'Status piket harus Selesai atau Belum.',
        ]);

        $piket->update([
            'id_anggota' => $request->id_anggota,
            'tanggal' => $request->tanggal,
            'tugas' => $request->tugas,
            'status' => $request->status,
        ]);

        return redirect()->route('piket.index')
            ->with('success', 'Jadwal piket berhasil diperbarui.');
    }

    /**
     * Mengubah status piket secara cepat (Selesai/Belum).
     * Akses: Admin & Guru.
     */
    public function updateStatus(Request $request, Piket $piket)
    {
        $request->validate([
            'status' => 'required|in:Selesai,Belum',
        ], [
            'status.in' => 'Status harus bernilai Selesai atau Belum.',
        ]);

        $piket->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Status piket berhasil diperbarui menjadi ' . $request->status . '.');
    }

    /**
     * Hapus jadwal piket.
     * Akses: Admin & Guru.
     */
    public function destroy(Piket $piket)
    {
        $piket->delete();

        return redirect()->route('piket.index')
            ->with('success', 'Jadwal piket berhasil dihapus.');
    }

    /**
     * Export rekapitulasi jadwal piket ke Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $query = Piket::with(['anggota.user']);

        if ($search = $request->input('q')) {
            $query->whereHas('anggota.user', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $tanggalFilter = $request->input('tanggal');
        if ($tanggalFilter && $tanggalFilter !== 'all') {
            $query->whereDate('tanggal', $tanggalFilter);
        }

        $statusFilter = $request->input('status');
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $items = $query->orderBy('tanggal', 'desc')->orderBy('id_piket', 'desc')->get();

        return Excel::download(new PiketExport($items), 'laporan-piket.xlsx');
    }

    /**
     * Export rekapitulasi jadwal piket ke PDF (.pdf).
     */
    public function exportPdf(Request $request)
    {
        $query = Piket::with(['anggota.user']);

        if ($search = $request->input('q')) {
            $query->whereHas('anggota.user', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $tanggalFilter = $request->input('tanggal');
        if ($tanggalFilter && $tanggalFilter !== 'all') {
            $query->whereDate('tanggal', $tanggalFilter);
        }

        $statusFilter = $request->input('status');
        if ($statusFilter && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $items = $query->orderBy('tanggal', 'desc')->orderBy('id_piket', 'desc')->get();

        $pdf = Pdf::loadView('exports.piket_pdf', compact('items', 'tanggalFilter', 'statusFilter'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('laporan-piket.pdf');
    }
}
