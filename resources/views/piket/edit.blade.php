@extends('layouts.dashboard')

@section('title', 'Ubah Jadwal Piket - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Jadwal Piket Kelas</h1>
        <p>Perbarui penugasan tugas kebersihan untuk siswa {{ $piket->anggota?->user?->nama ?? '' }}.</p>
    </div>
    <a href="{{ route('piket.index') }}" class="btn btn-outline">Kembali ke Daftar</a>
</div>

<div class="card" style="max-width: 650px;">
    <div class="card-header">
        <h2>Formulir Perubahan Piket</h2>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('piket.update', $piket->id_piket) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="id_anggota" class="form-label">Petugas Piket (Siswa) *</label>
                <select id="id_anggota" name="id_anggota" class="form-control" required>
                    @foreach ($anggotaList as $a)
                        <option value="{{ $a->id_anggota }}" {{ old('id_anggota', $piket->id_anggota) == $a->id_anggota ? 'selected' : '' }}>
                            {{ $a->user->nama ?? 'Tanpa Nama' }} (NIS: {{ $a->nis ?: '-' }})
                        </option>
                    @endforeach
                </select>
                @error('id_anggota') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="tanggal" class="form-label">Tanggal Kegiatan *</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $piket->tanggal->format('Y-m-d')) }}" class="form-control" required>
                @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="tugas" class="form-label">Tugas Spesifik *</label>
                <input 
                    type="text" 
                    id="tugas" 
                    name="tugas" 
                    value="{{ old('tugas', $piket->tugas) }}" 
                    class="form-control" 
                    required
                >
                @error('tugas') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status Penyelesaian *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Belum" {{ old('status', $piket->status) === 'Belum' ? 'selected' : '' }}>Belum</option>
                    <option value="Selesai" {{ old('status', $piket->status) === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
                @error('status') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('piket.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
