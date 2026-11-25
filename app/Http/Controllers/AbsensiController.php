<?php

namespace App\Http\Controllers;

use App\Exports\AbsensiExport;
use App\Models\Absensi;
use App\Models\Anggota;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    /**
     * Menampilkan daftar dan jurnal absensi.
     * User: hanya melihat riwayat dirinya sendiri.
     * Admin & Guru: melihat seluruh data presensi, form input, dan filter tanggal.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Tampilan Khusus Siswa (User)
        if ($user->isUser()) {
            $anggota = $user->anggota;
            $riwayatAbsensi = collect();
            $stats = [
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpa' => 0,
                'persentase' => 0,
            ];

            if ($anggota) {
                $riwayatAbsensi = Absensi::where('id_anggota', $anggota->id_anggota)
                    ->orderBy('tanggal', 'desc')
                    ->get();

                $stats['hadir'] = $riwayatAbsensi->where('status', 'Hadir')->count();
                $stats['izin'] = $riwayatAbsensi->where('status', 'Izin')->count();
                $stats['sakit'] = $riwayatAbsensi->where('status', 'Sakit')->count();
                $stats['alpa'] = $riwayatAbsensi->where('status', 'Alpa')->count();

                $total = $riwayatAbsensi->count();
                $stats['persentase'] = $total > 0 ? round(($stats['hadir'] / $total) * 100, 1) : 0;
            }

            return view('absensi.index_siswa', compact('riwayatAbsensi', 'stats'));
        }

        // 2. Tampilan Admin & Guru
        $tanggalFilter = $request->input('tanggal', date('Y-m-d'));
        $query = Absensi::with(['anggota.user']);

        if ($tanggalFilter !== 'all') {
            $query->where('tanggal', $tanggalFilter);
        }

        if ($search = $request->input('q')) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $absensiList = $query->orderBy('tanggal', 'desc')->orderBy('id_absensi', 'desc')->get();
        $anggotaList = Anggota::with('user')->orderBy('nis')->get();

        return view('absensi.index', compact('absensiList', 'anggotaList', 'tanggalFilter'));
    }

    /**
     * Simpan data presensi baru.
     * Akses: Admin & Guru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'id_anggota.required' => 'Pilih siswa terlebih dahulu.',
            'id_anggota.exists' => 'Siswa yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal kehadiran wajib diisi.',
            'status.required' => 'Status presensi wajib dipilih.',
            'status.in' => 'Status kehadiran harus Hadir, Izin, Sakit, atau Alpa.',
        ]);

        Absensi::create([
            'id_anggota' => $request->id_anggota,
            'tanggal' => $request->tanggal,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('absensi.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Presensi kehadiran siswa berhasil dicatat.');
    }

    /**
     * Form ubah presensi.
     * Akses: Admin & Guru.
     */
    public function edit(Absensi $absensi)
    {
        $absensi->load('anggota.user');
        $anggotaList = Anggota::with('user')->orderBy('nis')->get();

        return view('absensi.edit', compact('absensi', 'anggotaList'));
    }

    /**
     * Update data presensi.
     * Akses: Admin & Guru.
     */
    public function update(Request $request, Absensi $absensi)
    {
        $request->validate([
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Izin,Sakit,Alpa',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'id_anggota.required' => 'Pilih siswa terlebih dahulu.',
            'tanggal.required' => 'Tanggal kehadiran wajib diisi.',
            'status.required' => 'Status presensi wajib dipilih.',
            'status.in' => 'Status kehadiran harus Hadir, Izin, Sakit, atau Alpa.',
        ]);

        $absensi->update([
            'id_anggota' => $request->id_anggota,
            'tanggal' => $request->tanggal,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('absensi.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Data presensi berhasil diperbarui.');
    }

    /**
     * Hapus data presensi.
     * Akses: Admin & Guru.
     */
    public function destroy(Absensi $absensi)
    {
        $tanggal = $absensi->tanggal->format('Y-m-d');
        $absensi->delete();

        return redirect()->route('absensi.index', ['tanggal' => $tanggal])
            ->with('success', 'Data presensi berhasil dihapus.');
    }

    /**
     * Export rekapitulasi absensi ke Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $query = Absensi::with(['anggota.user']);
        $tanggalFilter = $request->input('tanggal');
        if ($tanggalFilter && $tanggalFilter !== 'all') {
            $query->whereDate('tanggal', $tanggalFilter);
        }
        if ($search = $request->input('q')) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('nama', 'like', "%{$search}%");
                  });
            });
        }
        $items = $query->orderBy('tanggal', 'desc')->orderBy('id_absensi', 'desc')->get();

        return Excel::download(new AbsensiExport($items), 'laporan-absensi.xlsx');
    }

    /**
     * Export rekapitulasi absensi ke PDF (.pdf).
     */
    public function exportPdf(Request $request)
    {
        $query = Absensi::with(['anggota.user']);
        $tanggalFilter = $request->input('tanggal');
        if ($tanggalFilter && $tanggalFilter !== 'all') {
            $query->whereDate('tanggal', $tanggalFilter);
        }
        if ($search = $request->input('q')) {
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('nama', 'like', "%{$search}%");
                  });
            });
        }
        $items = $query->orderBy('tanggal', 'desc')->orderBy('id_absensi', 'desc')->get();

        $pdf = Pdf::loadView('exports.absensi_pdf', compact('items', 'tanggalFilter'));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('laporan-absensi.pdf');
    }
}
