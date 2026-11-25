@extends('layouts.dashboard')

@section('title', 'Ubah Pengumuman - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Pengumuman</h1>
        <p>Perbarui informasi atau target sasaran pengumuman.</p>
    </div>
</div>

<div class="card" style="max-width: 720px;">
    <div class="card-header">
        <h2>Edit Pengumuman #{{ $pengumuman->id_pengumuman }}</h2>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('pengumuman.update', $pengumuman->id_pengumuman) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="judul" class="form-label">Judul Pengumuman *</label>
                <input type="text" id="judul" name="judul" value="{{ old('judul', $pengumuman->judul) }}" class="form-control" required>
                @error('judul') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label for="kategori" class="form-label">Kategori *</label>
                    <select id="kategori" name="kategori" class="form-control" required>
                        <option value="Informasi" {{ old('kategori', $pengumuman->kategori) === 'Informasi' ? 'selected' : '' }}>Informasi</option>
                        <option value="Kegiatan" {{ old('kategori', $pengumuman->kategori) === 'Kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                        <option value="Akademik" {{ old('kategori', $pengumuman->kategori) === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                        <option value="Penting" {{ old('kategori', $pengumuman->kategori) === 'Penting' ? 'selected' : '' }}>Penting</option>
                    </select>
                    @error('kategori') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="target" class="form-label">Target Sasaran *</label>
                    <select id="target" name="target" class="form-control" required>
                        <option value="Semua" {{ old('target', $pengumuman->target) === 'Semua' ? 'selected' : '' }}>Semua</option>
                        <option value="Guru" {{ old('target', $pengumuman->target) === 'Guru' ? 'selected' : '' }}>Khusus Guru</option>
                        <option value="Siswa" {{ old('target', $pengumuman->target) === 'Siswa' ? 'selected' : '' }}>Khusus Siswa</option>
                    </select>
                    @error('target') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="tanggal" class="form-label">Tanggal *</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', is_string($pengumuman->tanggal) ? $pengumuman->tanggal : $pengumuman->tanggal->format('Y-m-d')) }}" class="form-control" required>
                    @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="isi" class="form-label">Isi Lengkap Pengumuman *</label>
                <textarea id="isi" name="isi" rows="6" class="form-control" required>{{ old('isi', $pengumuman->isi) }}</textarea>
                @error('isi') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan Perubahan</button>
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline" style="text-align: center;">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
