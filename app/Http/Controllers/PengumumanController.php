<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengumumanController extends Controller
{
    /**
     * Menampilkan daftar pengumuman.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. Tampilan Khusus Siswa (Hanya melihat pengumuman target Semua atau Siswa)
        if ($user->isUser()) {
            $query = Pengumuman::with('user')
                ->whereIn('target', ['Semua', 'Siswa']);

            if ($search = $request->input('q')) {
                $query->where(function ($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhere('isi', 'like', "%{$search}%");
                });
            }

            if ($kategori = $request->input('kategori')) {
                if ($kategori !== 'all') {
                    $query->where('kategori', $kategori);
                }
            }

            $pengumumanList = $query->orderBy('tanggal', 'desc')->orderBy('id_pengumuman', 'desc')
                ->paginate(10)->withQueryString();

            return view('pengumuman.index_siswa', compact('pengumumanList'));
        }

        // 2. Tampilan Admin & Guru (Melihat seluruh pengumuman dengan filter lengkap)
        $query = Pengumuman::with('user');

        if ($search = $request->input('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('isi', 'like', "%{$search}%");
            });
        }

        if ($kategori = $request->input('kategori')) {
            if ($kategori !== 'all') {
                $query->where('kategori', $kategori);
            }
        }

        if ($target = $request->input('target')) {
            if ($target !== 'all') {
                $query->where('target', $target);
            }
        }

        $pengumumanList = $query->orderBy('tanggal', 'desc')->orderBy('id_pengumuman', 'desc')
            ->paginate(10)->withQueryString();

        return view('pengumuman.index', compact('pengumumanList'));
    }

    /**
     * Form tambah pengumuman baru.
     * Akses: Admin & Guru.
     */
    public function create()
    {
        return view('pengumuman.create');
    }

    /**
     * Simpan pengumuman baru ke database.
     * Akses: Admin & Guru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori' => 'required|in:Informasi,Kegiatan,Akademik,Penting',
            'target' => 'required|in:Semua,Guru,Siswa',
            'tanggal' => 'required|date',
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'kategori.required' => 'Kategori pengumuman wajib dipilih.',
            'kategori.in' => 'Kategori pengumuman tidak valid.',
            'target.required' => 'Target sasaran pengumuman wajib dipilih.',
            'target.in' => 'Target sasaran pengumuman tidak valid.',
            'tanggal.required' => 'Tanggal pengumuman wajib diisi.',
        ]);

        Pengumuman::create([
            'id_users' => Auth::id(),
            'judul' => $request->judul,
            'isi' => $request->isi,
            'kategori' => $request->kategori,
            'target' => $request->target,
            'tanggal' => $request->tanggal,
        ]);

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman baru berhasil dipublikasikan.');
    }

    /**
     * Menampilkan detail informasi pengumuman.
     */
    public function show(Pengumuman $pengumuman)
    {
        $user = Auth::user();

        // Validasi hak akses Siswa di backend
        if ($user->isUser() && !in_array($pengumuman->target, ['Semua', 'Siswa'], true)) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat pengumuman ini.');
        }

        $pengumuman->load('user');

        return view('pengumuman.show', compact('pengumuman'));
    }

    /**
     * Form edit pengumuman.
     * Akses: Admin & Guru.
     */
    public function edit(Pengumuman $pengumuman)
    {
        return view('pengumuman.edit', compact('pengumuman'));
    }

    /**
     * Update pengumuman.
     * Akses: Admin & Guru.
     */
    public function update(Request $request, Pengumuman $pengumuman)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'kategori' => 'required|in:Informasi,Kegiatan,Akademik,Penting',
            'target' => 'required|in:Semua,Guru,Siswa',
            'tanggal' => 'required|date',
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'kategori.required' => 'Kategori pengumuman wajib dipilih.',
            'kategori.in' => 'Kategori pengumuman tidak valid.',
            'target.required' => 'Target sasaran pengumuman wajib dipilih.',
            'target.in' => 'Target sasaran pengumuman tidak valid.',
            'tanggal.required' => 'Tanggal pengumuman wajib diisi.',
        ]);

        $pengumuman->update([
            'judul' => $request->judul,
            'isi' => $request->isi,
            'kategori' => $request->kategori,
            'target' => $request->target,
            'tanggal' => $request->tanggal,
        ]);

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Hapus pengumuman.
     * Akses: Admin & Guru.
     */
    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus.');
    }
}
