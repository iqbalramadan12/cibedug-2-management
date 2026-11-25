@extends('layouts.dashboard')

@section('title', 'Pengaturan Rayon')

@section('content')
<div class="card-header-clean">
    <div>
        <h2 class="card-title">Pengaturan Rayon Cibedug 2</h2>
        <p class="card-subtitle">Kelola foto kebersamaan, slogan, dan informasi identitas rayon di SMK Wikrama Bogor.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
    <!-- Form Upload Foto & Slogan -->
    <div class="card">
        <h3 class="card-title" style="font-size: 15px; margin-bottom: 16px;">Foto Kebersamaan & Slogan</h3>

        <form action="{{ route('pengaturan.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="slogan_rayon">Slogan / Deskripsi Singkat Rayon</label>
                <input type="text" name="slogan_rayon" id="slogan_rayon" class="form-input" 
                       value="{{ old('slogan_rayon', $slogan) }}" 
                       placeholder="Contoh: Tempat tumbuh, belajar, dan membangun kebersamaan.">
                <div class="form-hint">Slogan ini akan ditampilkan di atas banner foto kebersamaan pada dashboard seluruh pengguna.</div>
                @error('slogan_rayon')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="foto_hero">Unggah Foto Kebersamaan Baru (Landscape ~16:9)</label>
                <input type="file" name="foto_hero" id="foto_hero" class="form-input" accept="image/jpeg,image/png,image/jpg,image/webp">
                <div class="form-hint">Format didukung: JPG, JPEG, PNG, WEBP. Maksimal 5 MB. Disarankan orientasi landscape (16:9).</div>
                @error('foto_hero')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Preview & Status Foto -->
    <div class="card">
        <h3 class="card-title" style="font-size: 15px; margin-bottom: 16px;">Pratinjau Foto Kebersamaan Saat Ini</h3>
        
        <div style="border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--border-color); margin-bottom: 16px; background-color: #244C3D; min-height: 180px; display: flex; align-items: center; justify-content: center;">
            @if ($fotoHero)
                <img src="{{ asset('storage/' . $fotoHero) }}" alt="Foto Kebersamaan Rayon Cibedug 2" style="width: 100%; max-height: 240px; object-fit: cover;">
            @else
                <div style="text-align: center; padding: 30px 20px; color: #FFFFFF;">
                    <svg width="40" height="40" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin: 0 auto 10px; color: var(--secondary);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p style="font-size: 13.5px; font-weight: 600;">Menggunakan Tampilan Standar (Placeholder)</p>
                    <p style="font-size: 12px; color: var(--secondary); margin-top: 4px;">Belum ada foto yang diunggah.</p>
                </div>
            @endif
        </div>

        @if ($fotoHero)
            <form action="{{ route('pengaturan.destroyFoto') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto kebersamaan rayon dan kembali ke tampilan awal?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger-sm" style="width: 100%; justify-content: center;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Foto & Gunakan Standar</span>
                </button>
            </form>
        @endif

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light);">
            <div style="font-size: 12.5px; color: var(--text-muted); line-height: 1.5;">
                <strong style="color: var(--text-main);">Catatan Identitas:</strong><br>
                Rayon Cibedug 2 merupakan unit rayon kesiswaan di lingkungan <strong>SMK Wikrama Bogor</strong>. Setiap pembaruan foto dan identitas langsung terefleksi pada dashboard seluruh siswa, guru, dan admin.
            </div>
        </div>
    </div>
</div>
@endsection
