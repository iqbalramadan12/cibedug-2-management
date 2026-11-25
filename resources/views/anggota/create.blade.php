@extends('layouts.dashboard')

@section('title', 'Tambah Anggota Rayon')

@section('content')
<div class="card-header-clean">
    <div>
        <h2 class="card-title">Tambah Anggota Rayon Baru</h2>
        <p class="card-subtitle">Daftarkan siswa baru ke dalam unit Rayon Cibedug 2 SMK Wikrama Bogor.</p>
    </div>
    <a href="{{ route('anggota.index') }}" class="btn btn-outline">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>Kembali ke Direktori</span>
    </a>
</div>

<div class="card" style="max-width: 820px;">
    <form method="POST" action="{{ route('anggota.store') }}" enctype="multipart/form-data">
        @csrf

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="nama" class="form-label">Nama Lengkap Siswa *</label>
                <input type="text" id="nama" name="nama" value="{{ old('nama') }}" class="form-input" placeholder="contoh: Muhammad Rizky" required>
                @error('nama') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="nis" class="form-label">NIS / NISN *</label>
                <input type="text" id="nis" name="nis" value="{{ old('nis') }}" class="form-input" placeholder="contoh: 12209123" required>
                @error('nis') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="email" class="form-label">Email Siswa (untuk Login) *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="contoh: rizky@smkwikrama.sch.id" required>
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password Awal (Opsional)</label>
                <input type="text" id="password" name="password" value="{{ old('password') }}" class="form-input" placeholder="Default jika kosong: password123">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="kelas" class="form-label">Rombel / Kelas *</label>
                <input type="text" id="kelas" name="kelas" value="{{ old('kelas', 'PPLG XI-2') }}" class="form-input" placeholder="contoh: PPLG XI-2" required>
                @error('kelas') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="jurusan" class="form-label">Kompetensi Keahlian (Jurusan) *</label>
                <input type="text" id="jurusan" name="jurusan" value="{{ old('jurusan', 'Pengembangan Perangkat Lunak dan Gim') }}" class="form-input" placeholder="contoh: Pengembangan Perangkat Lunak dan Gim" required>
                @error('jurusan') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px;">
            <div class="form-group">
                <label for="no_hp" class="form-label">No. Telepon / WhatsApp Siswa / Orang Tua</label>
                <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp') }}" class="form-input" placeholder="contoh: 081234567890">
                @error('no_hp') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="foto" class="form-label">Foto Profil Siswa (Opsional)</label>
                <input type="file" id="foto" name="foto" class="form-input" accept="image/jpeg,image/png,image/jpg,image/webp">
                <div class="form-hint">Format: JPG, PNG, WEBP. Maks 3 MB.</div>
                @error('foto') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-top: 24px; display: flex; gap: 12px;">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Anggota</span>
            </button>
            <a href="{{ route('anggota.index') }}" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>
@endsection
