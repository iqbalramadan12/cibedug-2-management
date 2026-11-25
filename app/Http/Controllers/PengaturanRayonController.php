<?php

namespace App\Http\Controllers;

use App\Models\Pengaturan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PengaturanRayonController extends Controller
{
    /**
     * Tampilkan formulir pengaturan identitas dan foto hero Rayon Cibedug 2.
     * Akses: Admin.
     */
    public function index()
    {
        $namaRayon = Pengaturan::get('nama_rayon', 'Rayon Cibedug 2');
        $institusi = Pengaturan::get('institusi', 'SMK Wikrama Bogor');
        $slogan = Pengaturan::get('slogan_rayon', 'Tempat tumbuh, belajar, dan membangun kebersamaan.');
        $fotoHero = Pengaturan::get('foto_hero');

        return view('pengaturan.index', compact('namaRayon', 'institusi', 'slogan', 'fotoHero'));
    }

    /**
     * Simpan pembaruan foto hero dan slogan Rayon Cibedug 2.
     */
    public function update(Request $request)
    {
        $request->validate([
            'slogan_rayon' => 'nullable|string|max:255',
            'foto_hero' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120', // Maks 5MB
        ], [
            'foto_hero.image' => 'File harus berupa gambar valid.',
            'foto_hero.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'foto_hero.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        if ($request->has('slogan_rayon')) {
            Pengaturan::set('slogan_rayon', $request->input('slogan_rayon'));
        }

        if ($request->hasFile('foto_hero')) {
            // Hapus foto lama jika ada
            $oldFoto = Pengaturan::get('foto_hero');
            if ($oldFoto && Storage::disk('public')->exists($oldFoto)) {
                Storage::disk('public')->delete($oldFoto);
            }

            // Simpan foto baru
            $path = $request->file('foto_hero')->store('hero', 'public');
            Pengaturan::set('foto_hero', $path);
        }

        return redirect()->route('pengaturan.index')
            ->with('success', 'Pengaturan dan foto kebersamaan Rayon Cibedug 2 berhasil diperbarui.');
    }

    /**
     * Hapus foto hero kebersamaan rayon (kembali ke placeholder).
     */
    public function destroyFoto()
    {
        $oldFoto = Pengaturan::get('foto_hero');
        if ($oldFoto && Storage::disk('public')->exists($oldFoto)) {
            Storage::disk('public')->delete($oldFoto);
        }

        Pengaturan::set('foto_hero', null);

        return redirect()->route('pengaturan.index')
            ->with('success', 'Foto kebersamaan rayon berhasil dihapus dan kembali ke tampilan awal.');
    }
}
