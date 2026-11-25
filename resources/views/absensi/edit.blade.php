@extends('layouts.dashboard')

@section('title', 'Ubah Catatan Presensi - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Ubah Data Presensi</h1>
        <p>Perbarui status kehadiran siswa.</p>
    </div>
    <a href="{{ route('absensi.index', ['tanggal' => $absensi->tanggal->format('Y-m-d')]) }}" class="btn btn-outline">Kembali ke Jurnal</a>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h2>Formulir Perubahan Presensi</h2>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('absensi.update', $absensi->id_absensi) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="id_anggota" class="form-label">Siswa *</label>
                <select id="id_anggota" name="id_anggota" class="form-control" required>
                    @foreach ($anggotaList as $a)
                        <option value="{{ $a->id_anggota }}" {{ old('id_anggota', $absensi->id_anggota) == $a->id_anggota ? 'selected' : '' }}>
                            {{ $a->user->nama ?? 'Tanpa Nama' }} (NIS: {{ $a->nis ?: '-' }})
                        </option>
                    @endforeach
                </select>
                @error('id_anggota') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="tanggal" class="form-label">Tanggal Kehadiran *</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $absensi->tanggal->format('Y-m-d')) }}" class="form-control" required>
                @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status Presensi *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Hadir" {{ old('status', $absensi->status) === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="Izin" {{ old('status', $absensi->status) === 'Izin' ? 'selected' : '' }}>Izin</option>
                    <option value="Sakit" {{ old('status', $absensi->status) === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="Alpa" {{ old('status', $absensi->status) === 'Alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
                @error('status') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="keterangan" class="form-label">Keterangan / Alasan</label>
                <input type="text" id="keterangan" name="keterangan" value="{{ old('keterangan', $absensi->keterangan) }}" class="form-control">
                @error('keterangan') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-top: 24px; display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('absensi.index', ['tanggal' => $absensi->tanggal->format('Y-m-d')]) }}" class="btn btn-outline">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
