@extends('layouts.dashboard')

@section('title', 'Profil Anggota - ' . ($anggota->user->nama ?? 'Siswa'))

@section('content')
<div class="card-header-clean">
    <div>
        <h2 class="card-title">{{ isset($isSelfProfile) && $isSelfProfile ? 'Profil Siswa Saya' : 'Profil Anggota Rayon' }}</h2>
        <p class="card-subtitle">Informasi lengkap kesiswaan, statistik kehadiran, dan riwayat piket di Rayon Cibedug 2 SMK Wikrama Bogor.</p>
    </div>

    <div style="display: flex; gap: 10px;">
        @if (isset($isSelfProfile) && $isSelfProfile)
            <a href="{{ route('user.dashboard') }}" class="btn btn-outline">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Dashboard</span>
            </a>
        @else
            <a href="{{ route('anggota.index') }}" class="btn btn-outline">
                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Daftar Anggota</span>
            </a>
            @if (Auth::user()->isAdmin())
                <a href="{{ route('anggota.edit', $anggota->id_anggota) }}" class="btn btn-warning">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <span>Ubah Data</span>
                </a>
            @endif
        @endif
    </div>
</div>

<!-- Profil Card Header -->
<div class="profile-header-card">
    @if ($anggota->foto_url)
        <img src="{{ $anggota->foto_url }}" alt="{{ $anggota->user->nama ?? 'Siswa' }}" class="profile-avatar-lg">
    @else
        <div class="profile-avatar-placeholder-lg">
            {{ strtoupper(substr($anggota->user->nama ?? 'S', 0, 1)) }}
        </div>
    @endif

    <div class="profile-info-block">
        <h2>{{ $anggota->user->nama ?? '-' }}</h2>
        <div class="profile-badge-line">
            <span class="badge" style="background-color: var(--primary-light); color: var(--primary); font-size: 12px;">NIS: {{ $anggota->nis ?: '-' }}</span>
            <span class="badge" style="background-color: var(--secondary-light); color: #2E4E3E; font-size: 12px;">{{ $anggota->kelas ?: '-' }}</span>
            <span class="badge" style="background-color: var(--bg-main); border: 1px solid var(--border-color); color: var(--text-muted); font-size: 12px;">{{ $anggota->jurusan ?: '-' }}</span>
            <span class="badge" style="background-color: #EEF6F1; color: #1E4633; font-size: 12px;">Rayon Cibedug 2</span>
        </div>
    </div>
</div>

<!-- Statistik Kehadiran Ringkas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Tingkat Kehadiran</span>
            <div class="stat-icon green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $persentaseKehadiran }}%</div>
        <div class="stat-desc">{{ $summaryAbsensi['Hadir'] }} kali hadir dari total presensi</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Hadir</span>
            <div class="stat-icon green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--primary);">{{ $summaryAbsensi['Hadir'] }}</div>
        <div class="stat-desc">Pertemuan terlaksana</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Izin & Sakit</span>
            <div class="stat-icon amber">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--accent);">{{ $summaryAbsensi['Izin'] + $summaryAbsensi['Sakit'] }}</div>
        <div class="stat-desc">{{ $summaryAbsensi['Izin'] }} Izin &bull; {{ $summaryAbsensi['Sakit'] }} Sakit</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Alpa / Tanpa Keterangan</span>
            <div class="stat-icon red">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--danger);">{{ $summaryAbsensi['Alpa'] }}</div>
        <div class="stat-desc">Perlu pembinaan jika meningkat</div>
    </div>
</div>

<!-- Informasi Lengkap & Riwayat -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
    <!-- Rincian Identitas -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Rincian Informasi Siswa</h3>
        </div>
        <div>
            <div class="detail-item-row">
                <span class="detail-item-label">Nama Lengkap</span>
                <span class="detail-item-value">{{ $anggota->user->nama ?? '-' }}</span>
            </div>
            <div class="detail-item-row">
                <span class="detail-item-label">Nomor Induk Siswa (NIS)</span>
                <span class="detail-item-value">{{ $anggota->nis ?: '-' }}</span>
            </div>
            <div class="detail-item-row">
                <span class="detail-item-label">Rombongan Belajar (Kelas)</span>
                <span class="detail-item-value">{{ $anggota->kelas ?: '-' }}</span>
            </div>
            <div class="detail-item-row">
                <span class="detail-item-label">Kompetensi Keahlian (Jurusan)</span>
                <span class="detail-item-value">{{ $anggota->jurusan ?: '-' }}</span>
            </div>
            <div class="detail-item-row">
                <span class="detail-item-label">Alamat Email</span>
                <span class="detail-item-value">{{ $anggota->user->email ?? '-' }}</span>
            </div>
            <div class="detail-item-row">
                <span class="detail-item-label">Kontak No. HP / WhatsApp</span>
                <span class="detail-item-value">{{ $anggota->no_hp ?: '-' }}</span>
            </div>
            <div class="detail-item-row">
                <span class="detail-item-label">Institusi Pendidikan</span>
                <span class="detail-item-value">SMK Wikrama Bogor</span>
            </div>
            <div class="detail-item-row">
                <span class="detail-item-label">Unit Rayon Kesiswaan</span>
                <span class="detail-item-value">Rayon Cibedug 2</span>
            </div>
        </div>
    </div>

    <!-- Riwayat Piket Terakhir -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Riwayat Tugas Piket</h3>
            <span class="card-subtitle">10 catatan terakhir</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Hari / Tanggal</th>
                        <th>Tugas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayatPiket as $piket)
                        <tr>
                            <td>
                                <div style="font-weight: 600;">{{ $piket->hari }}</div>
                                <div style="font-size: 11.5px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($piket->tanggal)->isoFormat('D MMM Y') }}</div>
                            </td>
                            <td>{{ $piket->tugas }}</td>
                            <td>
                                @if ($piket->status === 'Selesai')
                                    <span class="badge badge-selesai">Selesai</span>
                                @else
                                    <span class="badge badge-belum">Belum</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 18px;">
                                Belum ada riwayat piket.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Riwayat Absensi Terakhir -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header-clean">
        <h3 class="card-title">Riwayat Kehadiran Siswa</h3>
        <span class="card-subtitle">10 catatan presensi terakhir</span>
    </div>
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Tanggal Presensi</th>
                    <th>Status Kehadiran</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayatAbsensi as $abs)
                    <tr>
                        <td style="font-weight: 600;">
                            {{ \Carbon\Carbon::parse($abs->tanggal)->isoFormat('dddd, D MMMM Y') }}
                        </td>
                        <td>
                            @if ($abs->status === 'Hadir')
                                <span class="badge badge-hadir">Hadir</span>
                            @elseif ($abs->status === 'Izin')
                                <span class="badge badge-izin">Izin</span>
                            @elseif ($abs->status === 'Sakit')
                                <span class="badge badge-sakit">Sakit</span>
                            @else
                                <span class="badge badge-alpa">Alpa</span>
                            @endif
                        </td>
                        <td style="color: var(--text-muted);">{{ $abs->keterangan ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 20px;">
                            Belum ada catatan presensi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
