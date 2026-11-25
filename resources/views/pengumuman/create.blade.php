@extends('layouts.dashboard')

@section('title', 'Buat Pengumuman Baru - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Buat Pengumuman Baru</h1>
        <p>Publikasikan warta atau informasi penting untuk siswa dan guru.</p>
    </div>
</div>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h2>Formulir Pengumuman</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('pengumuman.store') }}">
            @csrf

            <div class="form-group">
                <label for="judul" class="form-label">Judul Pengumuman *</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" class="form-control" placeholder="Contoh: Jadwal Ujian Akhir Semester Genap" required>
                @error('judul') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label for="kategori" class="form-label">Kategori *</label>
                    <select id="kategori" name="kategori" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="Informasi" {{ old('kategori') === 'Informasi' ? 'selected' : '' }}>Informasi</option>
                        <option value="Kegiatan" {{ old('kategori') === 'Kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="Akademik" {{ old('kategori') === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                        <option value="Penting" {{ old('kategori') === 'Penting' ? 'selected' : '' }}>Penting</option>
                    </select>
                    @error('kategori') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="target" class="form-label">Target Sasaran *</label>
                    <select id="target" name="target" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="Semua" {{ old('target') === 'Semua' ? 'selected' : '' }}>Semua (Guru & Siswa)</option>
                        <option value="Guru" {{ old('target') === 'Guru' ? 'selected' : '' }}>Khusus Guru</option>
                        <option value="Siswa" {{ old('target') === 'Siswa' ? 'selected' : '' }}>Khusus Siswa</option>
                    </select>
                    @error('target') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="tanggal" class="form-label">Tanggal *</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="form-control" required>
                    @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="isi" class="form-label">Isi Lengkap Pengumuman *</label>
                <textarea id="isi" name="isi" rows="6" class="form-control" placeholder="Tuliskan isi pengumuman secara rinci, lugas, dan jelas..." required>{{ old('isi') }}</textarea>
                @error('isi') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Publikasikan Pengumuman</button>
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline" style="text-align: center;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
