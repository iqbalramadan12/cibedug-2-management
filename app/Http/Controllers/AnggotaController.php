<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AnggotaController extends Controller
{
    /**
     * Menampilkan daftar anggota siswa dengan fitur pencarian.
     * Akses: Admin & Guru.
     */
    public function index(Request $request)
    {
        $query = Anggota::with('user');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('nis', 'like', "%{$search}%")
                  ->orWhere('kelas', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($sub) use ($search) {
                      $sub->where('nama', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $anggotaList = $query->orderBy('id_anggota', 'desc')->get();

        return view('anggota.index', compact('anggotaList'));
    }

    /**
     * Endpoint DataTables Server-Side untuk data anggota siswa.
     * Akses: Admin & Guru.
     */
    public function data(Request $request)
    {
        $query = Anggota::with('user')->select('anggota.*');

        return \Yajra\DataTables\Facades\DataTables::eloquent($query)
            ->addColumn('foto', function($row) {
                if ($row->foto_url) {
                    return '<img src="' . e($row->foto_url) . '" alt="' . e($row->user->nama ?? '') . '" class="table-avatar">';
                }
                $initial = strtoupper(substr($row->user->nama ?? 'S', 0, 1));
                return '<div class="table-avatar-placeholder">' . e($initial) . '</div>';
            })
            ->addColumn('nama', fn($row) => $row->user->nama ?? '-')
            ->addColumn('email', fn($row) => $row->user->email ?? '-')
            ->addColumn('action', function($row) {
                $btn = '<div class="table-action-group">';
                $btn .= '<a href="' . route('anggota.show', $row->id_anggota) . '" class="btn btn-secondary-sm btn-sm">Profil</a>';
                if (auth()->check() && auth()->user()->role === 'admin') {
                    $btn .= ' <a href="' . route('anggota.edit', $row->id_anggota) . '" class="btn btn-warning-sm btn-sm">Ubah</a>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['foto', 'action'])
            ->toJson();
    }

    /**
     * Form tambah anggota baru.
     * Akses: Admin.
     */
    public function create()
    {
        return view('anggota.create');
    }

    /**
     * Simpan data anggota dan user baru.
     * Akses: Admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'nullable|string|min:8',
            'nis' => 'required|string|max:30|unique:anggota,nis',
            'kelas' => 'required|string|max:20',
            'jurusan' => 'required|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'nis.required' => 'NIS/NISN wajib diisi.',
            'nis.unique' => 'NIS/NISN sudah terdaftar.',
            'kelas.required' => 'Kelas wajib diisi.',
            'jurusan.required' => 'Kompetensi keahlian/jurusan wajib diisi.',
            'password.min' => 'Password minimal 8 karakter jika diisi.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 3 MB.',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('anggota', 'public');
        }

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password ?: 'password123'),
            'role' => 'user',
        ]);

        Anggota::create([
            'id_users' => $user->id_users,
            'nis' => $request->nis,
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'no_hp' => $request->no_hp,
            'foto' => $fotoPath,
        ]);

        return redirect()->route('anggota.index')->with('success', 'Data anggota siswa berhasil ditambahkan.');
    }

    /**
     * Form ubah data anggota.
     * Akses: Admin.
     */
    public function edit(Anggota $anggota)
    {
        $anggota->load('user');
        return view('anggota.edit', compact('anggota'));
    }

    /**
     * Update data anggota dan user terkait.
     * Akses: Admin.
     */
    public function update(Request $request, Anggota $anggota)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,' . $anggota->id_users . ',id_users',
            'password' => 'nullable|string|min:8',
            'nis' => 'required|string|max:30|unique:anggota,nis,' . $anggota->id_anggota . ',id_anggota',
            'kelas' => 'required|string|max:20',
            'jurusan' => 'required|string|max:50',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh pengguna lain.',
            'nis.required' => 'NIS/NISN wajib diisi.',
            'nis.unique' => 'NIS/NISN sudah terdaftar.',
            'kelas.required' => 'Kelas wajib diisi.',
            'jurusan.required' => 'Kompetensi keahlian wajib diisi.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 3 MB.',
        ]);

        $user = $anggota->user;
        $userData = [
            'nama' => $request->nama,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        if ($user) {
            $user->update($userData);
        }

        $anggotaData = [
            'nis' => $request->nis,
            'kelas' => $request->kelas,
            'jurusan' => $request->jurusan,
            'no_hp' => $request->no_hp,
        ];

        // Hapus foto jika checkbox dicentang
        if ($request->boolean('hapus_foto')) {
            if ($anggota->foto && Storage::disk('public')->exists($anggota->foto)) {
                Storage::disk('public')->delete($anggota->foto);
            }
            $anggotaData['foto'] = null;
        }

        // Upload foto baru jika ada
        if ($request->hasFile('foto')) {
            if ($anggota->foto && Storage::disk('public')->exists($anggota->foto)) {
                Storage::disk('public')->delete($anggota->foto);
            }
            $anggotaData['foto'] = $request->file('foto')->store('anggota', 'public');
        }

        $anggota->update($anggotaData);

        return redirect()->route('anggota.index')->with('success', 'Data anggota siswa berhasil diperbarui.');
    }

    /**
     * Tampilkan detail profil anggota siswa beserta rekapitulasi kehadiran dan riwayat piket.
     * Akses: Admin, Guru, dan Siswa terkait.
     */
    public function show(Anggota $anggota)
    {
        $anggota->load([
            'user',
            'absensi' => fn($q) => $q->orderBy('tanggal', 'desc'),
            'piket' => fn($q) => $q->orderBy('tanggal', 'desc')
        ]);

        $summaryAbsensi = [
            'Hadir' => $anggota->absensi->where('status', 'Hadir')->count(),
            'Izin' => $anggota->absensi->where('status', 'Izin')->count(),
            'Sakit' => $anggota->absensi->where('status', 'Sakit')->count(),
            'Alpa' => $anggota->absensi->where('status', 'Alpa')->count(),
        ];

        $totalAbsensi = $anggota->absensi->count();
        $persentaseKehadiran = $totalAbsensi > 0
            ? round(($summaryAbsensi['Hadir'] / $totalAbsensi) * 100, 1)
            : 100;

        $riwayatAbsensi = $anggota->absensi->take(10);
        $riwayatPiket = $anggota->piket->take(10);

        return view('anggota.show', compact('anggota', 'summaryAbsensi', 'persentaseKehadiran', 'riwayatAbsensi', 'riwayatPiket'));
    }

    /**
     * Halaman profil mandiri untuk Siswa yang sedang login.
     * Akses: Siswa (Role: user).
     */
    public function profilSaya()
    {
        $user = Auth::user();
        $anggota = $user->anggota;

        if (!$anggota) {
            return redirect()->route('user.dashboard')->with('error', 'Data profil siswa belum terdaftar.');
        }

        $anggota->load([
            'user',
            'absensi' => fn($q) => $q->orderBy('tanggal', 'desc'),
            'piket' => fn($q) => $q->orderBy('tanggal', 'desc')
        ]);

        $summaryAbsensi = [
            'Hadir' => $anggota->absensi->where('status', 'Hadir')->count(),
            'Izin' => $anggota->absensi->where('status', 'Izin')->count(),
            'Sakit' => $anggota->absensi->where('status', 'Sakit')->count(),
            'Alpa' => $anggota->absensi->where('status', 'Alpa')->count(),
        ];

        $totalAbsensi = $anggota->absensi->count();
        $persentaseKehadiran = $totalAbsensi > 0
            ? round(($summaryAbsensi['Hadir'] / $totalAbsensi) * 100, 1)
            : 100;

        $riwayatAbsensi = $anggota->absensi->take(10);
        $riwayatPiket = $anggota->piket->take(10);
        $isSelfProfile = true;

        return view('anggota.show', compact('anggota', 'summaryAbsensi', 'persentaseKehadiran', 'riwayatAbsensi', 'riwayatPiket', 'isSelfProfile'));
    }

    /**
     * Hapus data anggota dan akun user terkait.
     * Akses: Admin.
     */
    public function destroy(Anggota $anggota)
    {
        if ($anggota->foto && Storage::disk('public')->exists($anggota->foto)) {
            Storage::disk('public')->delete($anggota->foto);
        }

        $user = $anggota->user;
        $anggota->delete();
        if ($user) {
            $user->delete();
        }

        return redirect()->route('anggota.index')->with('success', 'Data anggota berhasil dihapus.');
    }
}
