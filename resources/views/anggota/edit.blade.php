@extends('layouts.dashboard')

@section('title', 'Ubah Data Anggota')

@section('content')
<div class="card-header-clean">
    <div>
        <h2 class="card-title">Ubah Data Anggota Rayon</h2>
        <p class="card-subtitle">Perbarui informasi biodata siswa {{ $anggota->user->nama ?? '' }}.</p>
    </div>
    <a href="{{ route('anggota.index') }}" class="btn btn-outline">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Direktori</span>
    </a>
</div>

<div class="card" style="max-width: 820px;">
    <form method="POST" action="{{ route('anggota.update', $anggota->id_anggota) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="nama" class="form-label">Nama Lengkap Siswa *</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama', $anggota->user->nama ?? '') }}" class="form-input" required>
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="nis" class="form-label">NIS / NISN *</label>
                <input type="text" id="nis" name="nis" value="{{ old('nis', $anggota->nis) }}" class="form-input" required>
                @error('nis') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="email" class="form-label">Email Siswa (untuk Login) *</label>
                <input type="email" id="email" name="email" value="{{ old('email', $anggota->user->email ?? '') }}" class="form-input" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password Baru (Kosongkan jika tidak diganti)</label>
                <input type="text" id="password" name="password" value="{{ old('password') }}" class="form-input" placeholder="Minimal 8 karakter jika ingin ganti">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="kelas" class="form-label">Rombel / Kelas *</label>
                <input type="text" id="kelas" name="kelas" value="{{ old('kelas', $anggota->kelas) }}" class="form-input" required>
                @error('kelas') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="jurusan" class="form-label">Kompetensi Keahlian (Jurusan) *</label>
                <input type="text" id="jurusan" name="jurusan" value="{{ old('jurusan', $anggota->jurusan) }}" class="form-input" required>
                @error('jurusan') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="no_hp" class="form-label">No. Telepon / WhatsApp Siswa / Orang Tua</label>
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $anggota->no_hp) }}" class="form-input">
                @error('no_hp') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="foto" class="form-label">Ganti Foto Profil Siswa</label>
                <input type="file" id="foto" name="foto" class="form-input" accept="image/jpeg,image/png,image/jpg,image/webp">
                <div class="form-hint">Format: JPG, PNG, WEBP. Maks 3 MB.</div>
                @error('foto') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Pratinjau Foto Profil Jika Ada -->
        @if ($anggota->foto_url)
            <div style="background-color: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px;">
                <img src="{{ $anggota->foto_url }}" alt="{{ $anggota->user->nama ?? '' }}" style="width: 60px; height: 60px; border-radius: var(--radius-md); object-fit: cover; border: 1px solid var(--border-color);">
                <div>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; cursor: pointer; color: var(--danger);">
                        <input type="checkbox" name="hapus_foto" value="1">
                        <span>Hapus foto saat ini dan gunakan inisial avatar</span>
                    </label>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">Centang opsi ini jika ingin menghapus berkas foto dari penyimpanan.</div>
                </div>
            </div>
        @endif

        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perubahan</span>
            </button>
            <a href="{{ route('anggota.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
