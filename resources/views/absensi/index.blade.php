@extends('layouts.dashboard')

@section('title', 'Jurnal Absensi & Presensi - Cibedug 2 Management')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1>Jurnal Absensi & Presensi</h1>
        <p>Pencatatan kehadiran murid kelas harian secara teratur dan tertib.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('absensi.export.excel', request()->all()) }}" class="btn btn-outline btn-sm" style="background: #10b981; color: white; border-color: #10b981;">
            <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
        <a href="{{ route('absensi.export.pdf', request()->all()) }}" class="btn btn-outline btn-sm" style="background: #ef4444; color: white; border-color: #ef4444;">
            <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
        </a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 360px 1fr; gap: 24px; align-items: start;">
    <!-- Form Input Presensi -->
    <div class="card">
        <div class="card-header">
            <h2>Input Kehadiran Siswa</h2>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('absensi.store') }}">
                @csrf

                <div class="form-group">
                    <label for="id_anggota" class="form-label">Nama Siswa *</label>
                    <select id="id_anggota" name="id_anggota" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>
                        @foreach ($anggotaList as $a)
                            <option value="{{ $a->id_anggota }}" {{ old('id_anggota') == $a->id_anggota ? 'selected' : '' }}>
                                {{ $a->user->nama ?? 'Tanpa Nama' }} (NIS: {{ $a->nis ?: '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_anggota') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="tanggal" class="form-label">Tanggal Kehadiran *</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" class="form-control" required>
                    @error('tanggal') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status Presensi *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="Hadir" {{ old('status') === 'Hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="Izin" {{ old('status') === 'Izin' ? 'selected' : '' }}>Izin</option>
                        <option value="Sakit" {{ old('status') === 'Sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="Alpa" {{ old('status') === 'Alpa' ? 'selected' : '' }}>Alpa</option>
                    </select>
                    @error('status') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="keterangan" class="form-label">Keterangan / Alasan</label>
                    <input type="text" id="keterangan" name="keterangan" value="{{ old('keterangan') }}" class="form-control" placeholder="contoh: Surat dokter terlampir">
                    @error('keterangan') <div style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    Simpan Kehadiran
                </button>
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Absensi -->
    <div class="card">
        <div class="card-header">
            <h2>Daftar Riwayat Presensi</h2>

            <!-- Filter Tanggal & Pencarian -->
            <form method="GET" action="{{ route('absensi.index') }}" class="search-form" style="gap: 10px;">
                <input 
                    type="date" 
                    name="tanggal" 
                    value="{{ $tanggalFilter !== 'all' ? $tanggalFilter : '' }}" 
                    class="form-control" 
                    style="width: 150px; height: 34px; font-size: 12.5px;"
                >
                <input 
                    type="text" 
                    name="q" 
                    value="{{ request('q') }}" 
                    class="search-input" 
                    style="width: 160px; height: 34px;" 
                    placeholder="Cari siswa..."
                >
                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <a href="{{ route('absensi.index', ['tanggal' => 'all']) }}" class="btn btn-outline btn-sm" style="color: #64748b;">Semua</a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensiList as $absensi)
                        <tr>
                            <td>
                                <strong style="color: #0f172a;">{{ $absensi->anggota?->user?->nama ?? 'Siswa Tidak Ditemukan' }}</strong>
                                <div style="font-size: 11.5px; color: #64748b;">NIS: {{ $absensi->anggota?->nis ?: '-' }}</div>
                            </td>
                            <td>{{ $absensi->tanggal->format('d M Y') }}</td>
                            <td>
                                <span class="badge-status {{ strtolower($absensi->status) }}">
                                    {{ $absensi->status }}
                                </span>
                            </td>
                            <td style="color: #64748b;">{{ $absensi->keterangan ?: '-' }}</td>
                            <td style="text-align: center; white-space: nowrap;">
                                <a href="{{ route('absensi.edit', $absensi->id_absensi) }}" class="btn btn-warning-sm btn-sm">Ubah</a>
                                <form action="{{ route('absensi.destroy', $absensi->id_absensi) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus catatan presensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger-sm btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94a3b8; padding: 32px;">
                                Belum ada data presensi untuk kriteria tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
