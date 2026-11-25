@extends('layouts.dashboard')

@section('title', 'Tambah Jadwal Piket - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Tambah Jadwal Piket Kelas</h1>
        <p>Tetapkan jadwal dan tanggung jawab tugas kebersihan harian.</p>
    </div>
    <a href="{{ route('piket.index') }}" class="btn btn-outline">Kembali ke Daftar</a>
</div>

<div class="card" style="max-width: 650px;">
    <div class="card-header">
        <h2>Formulir Penugasan Piket</h2>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('piket.store') }}">
            @csrf

            <div class="form-group">
                <label for="id_anggota" class="form-label">Petugas Piket (Siswa) *</label>
                <select id="id_anggota" name="id_anggota" class="form-control" required>
                    <option value="">-- Pilih Siswa Petugas --</option>
                    @foreach ($anggotaList as $a)
                        <option value="{{ $a->id_anggota }}" {{ old('id_anggota') == $a->id_anggota ? 'selected' : '' }}>
                            {{ $a->user->nama ?? 'Tanpa Nama' }} (NIS: {{ $a->nis ?: '-' }})
                        </option>
                    @endforeach
                </select>
                @error('id_anggota') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="tanggal" class="form-label">Tanggal Kegiatan *</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="form-control" required>
                @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="tugas" class="form-label">Tugas Spesifik *</label>
                <input 
                    type="text" 
                    id="tugas" 
                    name="tugas" 
                    value="{{ old('tugas') }}" 
                    class="form-control" 
                    placeholder="contoh: Menyapu lantai & membuang sampah" 
                    required
                >
                <span style="font-size: 12px; color: #64748b; margin-top: 3px; display: block;">
                    Contoh: "Menyapu ruang depan & membuang sampah", "Membersihkan papan tulis & kaca", dll.
                </span>
                @error('tugas') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status Penyelesaian *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Belum" {{ old('status', 'Belum') === 'Belum' ? 'selected' : '' }}>Belum</option>
                    <option value="Selesai" {{ old('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                @error('status') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">Simpan Jadwal Piket</button>
                <a href="{{ route('piket.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
