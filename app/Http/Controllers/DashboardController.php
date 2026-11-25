<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kas;
use App\Models\Pengumuman;
use App\Models\Piket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard Siswa (Role: user)
     */
    public function userDashboard()
    {
        $user = Auth::user()->load('anggota');
        $anggota = $user->anggota;

        $stats = [
            'hadir' => 0,
            'izin_sakit' => 0,
            'alpa' => 0,
            'persentase' => 100,
        ];

        $riwayatAbsensi = collect();
        $piketHariIni = null;
        $upcomingPikets = collect();

        // Data Chart Kehadiran Pribadi & Piket Pribadi
        $chartAbsensi = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Alpa' => 0];
        $chartPiket = ['Selesai' => 0, 'Belum' => 0];

        if ($anggota) {
            $absensi = Absensi::where('id_anggota', $anggota->id_anggota)
                ->orderBy('tanggal', 'desc')
                ->get();

            $stats['hadir'] = $absensi->where('status', 'Hadir')->count();
            $stats['izin_sakit'] = $absensi->whereIn('status', ['Izin', 'Sakit'])->count();
            $stats['alpa'] = $absensi->where('status', 'Alpa')->count();

            $total = $absensi->count();
            $stats['persentase'] = $total > 0 ? round(($stats['hadir'] / $total) * 100, 1) : 100;
            $riwayatAbsensi = $absensi->take(5);

            $today = date('Y-m-d');
            $piketHariIni = Piket::where('id_anggota', $anggota->id_anggota)
                ->where('tanggal', $today)
                ->first();

            $upcomingPikets = Piket::where('id_anggota', $anggota->id_anggota)
                ->orderBy('tanggal', 'desc')
                ->take(3)
                ->get();

            $chartAbsensi = [
                'Hadir' => $stats['hadir'],
                'Izin' => $absensi->where('status', 'Izin')->count(),
                'Sakit' => $absensi->where('status', 'Sakit')->count(),
                'Alpa' => $stats['alpa'],
            ];

            $allPiket = Piket::where('id_anggota', $anggota->id_anggota)->get();
            $chartPiket = [
                'Selesai' => $allPiket->where('status', 'Selesai')->count(),
                'Belum' => $allPiket->where('status', 'Belum')->count(),
            ];
        }

        // Pengumuman terbaru untuk siswa
        $recentPengumuman = Pengumuman::with('user')
            ->whereIn('target', ['Semua', 'Siswa'])
            ->orderBy('tanggal', 'desc')
            ->take(3)
            ->get();

        return view('dashboards.user', compact(
            'user',
            'stats',
            'riwayatAbsensi',
            'piketHariIni',
            'upcomingPikets',
            'chartAbsensi',
            'chartPiket',
            'recentPengumuman'
        ));
    }

    /**
     * Dashboard Guru / Pembimbing Siswa (Role: guru)
     */
    public function guruDashboard()
    {
        $user = Auth::user();
        $totalSiswa = Anggota::count();

        $today = date('Y-m-d');
        $hadirToday = Absensi::where('tanggal', $today)->where('status', 'Hadir')->count();
        $absensiToday = Absensi::with('anggota.user')
            ->where('tanggal', $today)
            ->get();

        $piketToday = Piket::with('anggota.user')
            ->where('tanggal', $today)
            ->get();

        // Data Chart Kehadiran Kelas Keseluruhan
        $chartAbsensi = [
            'Hadir' => Absensi::where('status', 'Hadir')->count(),
            'Izin' => Absensi::where('status', 'Izin')->count(),
            'Sakit' => Absensi::where('status', 'Sakit')->count(),
            'Alpa' => Absensi::where('status', 'Alpa')->count(),
        ];

        // Data Chart Penyelesaian Piket
        $chartPiket = [
            'Selesai' => Piket::where('status', 'Selesai')->count(),
            'Belum' => Piket::where('status', 'Belum')->count(),
        ];

        // Pengumuman terbaru untuk guru
        $recentPengumuman = Pengumuman::with('user')
            ->whereIn('target', ['Semua', 'Guru'])
            ->orderBy('tanggal', 'desc')
            ->take(3)
            ->get();

        return view('dashboards.guru', compact(
            'user',
            'totalSiswa',
            'hadirToday',
            'absensiToday',
            'piketToday',
            'chartAbsensi',
            'chartPiket',
            'recentPengumuman'
        ));
    }

    /**
     * Dashboard Admin (Role: admin)
     */
    public function adminDashboard()
    {
        $user = Auth::user();
        $totalSiswa = Anggota::count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalUser = User::count();

        $today = date('Y-m-d');
        $hadirToday = Absensi::where('tanggal', $today)->where('status', 'Hadir')->count();
        $piketTodayCount = Piket::where('tanggal', $today)->count();
        $totalPiket = Piket::count();

        // 1. Chart Kehadiran Siswa
        $chartAbsensi = [
            'Hadir' => Absensi::where('status', 'Hadir')->count(),
            'Izin' => Absensi::where('status', 'Izin')->count(),
            'Sakit' => Absensi::where('status', 'Sakit')->count(),
            'Alpa' => Absensi::where('status', 'Alpa')->count(),
        ];

        // 2. Chart Status Piket
        $chartPiket = [
            'Selesai' => Piket::where('status', 'Selesai')->count(),
            'Belum' => Piket::where('status', 'Belum')->count(),
        ];

        // 3. Chart Pemasukan & Pengeluaran Kas
        $chartKas = [
            'Pemasukan' => (float) Kas::where('jenis', 'Pemasukan')->sum('nominal'),
            'Pengeluaran' => (float) Kas::where('jenis', 'Pengeluaran')->sum('nominal'),
        ];

        // 4. Statistik Jumlah Anggota per Jurusan
        $chartJurusan = Anggota::selectRaw('jurusan, count(*) as total')
            ->groupBy('jurusan')
            ->pluck('total', 'jurusan')
            ->toArray();

        if (empty($chartJurusan)) {
            $chartJurusan = ['Rekayasa Perangkat Lunak' => $totalSiswa];
        }

        // Pengumuman terbaru
        $recentPengumuman = Pengumuman::with('user')
            ->orderBy('tanggal', 'desc')
            ->take(3)
            ->get();

        return view('dashboards.admin', compact(
            'user',
            'totalSiswa',
            'totalGuru',
            'totalUser',
            'hadirToday',
            'piketTodayCount',
            'totalPiket',
            'chartAbsensi',
            'chartPiket',
            'chartKas',
            'chartJurusan',
            'recentPengumuman'
        ));
    }
}
