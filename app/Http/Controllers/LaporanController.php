<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kas;
use App\Models\Piket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    /**
     * Menampilkan dashboard laporan dan rekapitulasi terpadu.
     * Hak akses: Admin & Guru. Siswa dilarang (403).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Validasi hak akses di backend: Siswa dilarang
        if ($user->isUser()) {
            abort(403, 'Akses ditolak. Siswa tidak memiliki izin melihat laporan rekapitulasi kelas.');
        }

        $tab = $request->input('tab', 'overview');
        $tanggalAwal = $request->input('tanggal_awal');
        $tanggalAkhir = $request->input('tanggal_akhir');
        $bulan = $request->input('bulan');
        $tahun = $request->input('tahun', date('Y'));

        // Query Builder dasar untuk masing-masing modul dengan filter tanggal
        $absensiQuery = Absensi::with(['anggota.user']);
        $piketQuery = Piket::with(['anggota.user']);
        $kasQuery = Kas::with('user');

        if ($tanggalAwal && $tanggalAkhir) {
            $absensiQuery->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            $piketQuery->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
            $kasQuery->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
        } elseif ($bulan) {
            $absensiQuery->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            $piketQuery->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            $kasQuery->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        } elseif ($tahun) {
            $absensiQuery->whereYear('tanggal', $tahun);
            $piketQuery->whereYear('tanggal', $tahun);
            $kasQuery->whereYear('tanggal', $tahun);
        }

        // 1. Agregasi Statistik Absensi
        $totalAbsensi = (clone $absensiQuery)->count();
        $totalHadir = (clone $absensiQuery)->where('status', 'Hadir')->count();
        $totalIzin = (clone $absensiQuery)->where('status', 'Izin')->count();
        $totalSakit = (clone $absensiQuery)->where('status', 'Sakit')->count();
        $totalAlpa = (clone $absensiQuery)->where('status', 'Alpa')->count();
        $persentaseHadir = $totalAbsensi > 0 ? round(($totalHadir / $totalAbsensi) * 100, 1) : 0;

        // 2. Agregasi Statistik Piket
        $totalPiket = (clone $piketQuery)->count();
        $piketSelesai = (clone $piketQuery)->where('status', 'Selesai')->count();
        $piketBelum = (clone $piketQuery)->where('status', 'Belum')->count();
        $persentasePiket = $totalPiket > 0 ? round(($piketSelesai / $totalPiket) * 100, 1) : 0;

        // 3. Agregasi Statistik Keuangan Kas
        $totalPemasukan = (clone $kasQuery)->where('jenis', 'Pemasukan')->sum('nominal');
        $totalPengeluaran = (clone $kasQuery)->where('jenis', 'Pengeluaran')->sum('nominal');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        // Data detail per tab
        $rekapSiswa = collect();
        $detailList = collect();

        if ($tab === 'absensi') {
            $detailList = $absensiQuery->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();
            
            // Rekap persentase kehadiran per siswa
            $rekapSiswa = Anggota::with(['user', 'absensi' => function ($q) use ($tanggalAwal, $tanggalAkhir, $bulan, $tahun) {
                if ($tanggalAwal && $tanggalAkhir) {
                    $q->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                } elseif ($bulan) {
                    $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
                } elseif ($tahun) {
                    $q->whereYear('tanggal', $tahun);
                }
            }])->get()->map(function ($anggota) {
                $abs = $anggota->absensi;
                $tot = $abs->count();
                $h = $abs->where('status', 'Hadir')->count();
                return [
                    'nis' => $anggota->nis,
                    'nama' => $anggota->user->nama ?? '-',
                    'hadir' => $h,
                    'izin' => $abs->where('status', 'Izin')->count(),
                    'sakit' => $abs->where('status', 'Sakit')->count(),
                    'alpa' => $abs->where('status', 'Alpa')->count(),
                    'total' => $tot,
                    'persen' => $tot > 0 ? round(($h / $tot) * 100, 1) : 0,
                ];
            });
        } elseif ($tab === 'piket') {
            $detailList = $piketQuery->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();

            // Rekap piket per siswa
            $rekapSiswa = Anggota::with(['user', 'piket' => function ($q) use ($tanggalAwal, $tanggalAkhir, $bulan, $tahun) {
                if ($tanggalAwal && $tanggalAkhir) {
                    $q->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);
                } elseif ($bulan) {
                    $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
                } elseif ($tahun) {
                    $q->whereYear('tanggal', $tahun);
                }
            }])->get()->map(function ($anggota) {
                $pks = $anggota->piket;
                $tot = $pks->count();
                $selesai = $pks->where('status', 'Selesai')->count();
                return [
                    'nis' => $anggota->nis,
                    'nama' => $anggota->user->nama ?? '-',
                    'total' => $tot,
                    'selesai' => $selesai,
                    'belum' => $pks->where('status', 'Belum')->count(),
                    'persen' => $tot > 0 ? round(($selesai / $tot) * 100, 1) : 0,
                ];
            });
        } elseif ($tab === 'kas') {
            $detailList = $kasQuery->orderBy('tanggal', 'desc')->paginate(15)->withQueryString();
        }

        $filters = compact('tab', 'tanggalAwal', 'tanggalAkhir', 'bulan', 'tahun');

        return view('laporan.index', compact(
            'tab',
            'filters',
            'totalAbsensi',
            'totalHadir',
            'totalIzin',
            'totalSakit',
            'totalAlpa',
            'persentaseHadir',
            'totalPiket',
            'piketSelesai',
            'piketBelum',
            'persentasePiket',
            'totalPemasukan',
            'totalPengeluaran',
            'saldoAkhir',
            'detailList',
            'rekapSiswa'
        ));
    }
}
