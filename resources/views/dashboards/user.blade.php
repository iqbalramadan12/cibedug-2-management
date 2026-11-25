@extends('layouts.dashboard')

@section('title', 'Dashboard Siswa')

@section('content')
<!-- Hero Foto Kebersamaan Rayon -->
@include('components.hero_rayon')

<div class="card-header-clean">
    <div>
        <h2 class="card-title">Selamat Datang, {{ $user->nama }}</h2>
        <p class="card-subtitle">Informasi kehadiran, tugas piket rayon, dan warta kegiatan di Rayon Cibedug 2 SMK Wikrama Bogor.</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <a href="{{ route('user.profil') }}" class="btn btn-primary btn-sm">Profil Saya</a>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-sm">Pengumuman</a>
        <a href="{{ route('kas.index') }}" class="btn btn-outline btn-sm">Kas Rayon</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Kehadiran Saya</span>
            <div class="stat-icon green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--primary);">{{ $stats['hadir'] }} Hari</div>
        <div class="stat-desc">Tingkat kehadiran: {{ $stats['persentase'] }}%</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Izin & Sakit</span>
            <div class="stat-icon amber">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--accent);">{{ $stats['izin_sakit'] }} Hari</div>
        <div class="stat-desc">Tercatat dengan keterangan</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Status Tugas Piket</span>
            <div class="stat-icon teal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--info);">
            @if ($piketHariIni)
                {{ $piketHariIni->status }}
            @else
                {{ $upcomingPikets->count() }} Jadwal
            @endif
        </div>
        <div class="stat-desc">{{ $piketHariIni ? 'Tugas Piket Hari Ini' : 'Jadwal mendatang' }}</div>
    </div>
</div>

<!-- Visualisasi Statistik Pribadi Siswa -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Statistik Kehadiran Pribadi</h3>
        </div>
        <div style="height: 190px; position: relative;">
            <canvas id="siswaChartAbsensi"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Penyelesaian Piket Pribadi</h3>
        </div>
        <div style="height: 190px; position: relative;">
            <canvas id="siswaChartPiket"></canvas>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; align-items: start; margin-bottom: 24px;">
    <!-- Ringkasan Profil & Piket Hari Ini -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <div class="card">
            <div class="card-header-clean">
                <h3 class="card-title">Identitas Siswa</h3>
                <a href="{{ route('user.profil') }}" class="btn btn-secondary-sm btn-sm">Lihat Penuh</a>
            </div>
            <div>
                <div class="detail-item-row">
                    <span class="detail-item-label">Nama:</span>
                    <span class="detail-item-value">{{ $user->nama }}</span>
                </div>
                <div class="detail-item-row">
                    <span class="detail-item-label">NIS:</span>
                    <span class="detail-item-value" style="color: var(--primary);">{{ $user->anggota?->nis ?? '-' }}</span>
                </div>
                <div class="detail-item-row">
                    <span class="detail-item-label">Kelas:</span>
                    <span class="detail-item-value">{{ $user->anggota?->kelas ?? '-' }}</span>
                </div>
                <div class="detail-item-row">
                    <span class="detail-item-label">Jurusan:</span>
                    <span class="detail-item-value">{{ $user->anggota?->jurusan ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header-clean">
                <h3 class="card-title">Jadwal Piket Hari Ini</h3>
            </div>
            <div>
                @if ($piketHariIni)
                    <div style="background-color: var(--bg-main); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px;">
                        <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">{{ $piketHariIni->tanggal->translatedFormat('l, d F Y') }}</div>
                        <div style="font-weight: 600; color: var(--text-main); margin-bottom: 8px;">{{ $piketHariIni->tugas }}</div>
                        <div>
                            Status: 
                            @if ($piketHariIni->status === 'Selesai')
                                <span class="badge badge-selesai">Selesai</span>
                            @else
                                <span class="badge badge-belum">Belum</span>
                            @endif
                        </div>
                    </div>
                @else
                    <p style="font-size: 13px; color: var(--text-muted);">
                        Tidak ada penugasan piket untuk Anda hari ini.
                    </p>
                @endif
                <div style="margin-top: 14px;">
                    <a href="{{ route('piket.index') }}" class="btn btn-outline btn-sm" style="width: 100%;">Buka Jadwal Piket</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Presensi Terbaru -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Riwayat Presensi Terbaru</h3>
            <a href="{{ route('absensi.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayatAbsensi as $abs)
                        <tr>
                            <td style="font-weight: 600;">{{ $abs->tanggal->translatedFormat('d M Y') }}</td>
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
                                Belum ada riwayat kehadiran tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Warta / Pengumuman untuk Siswa -->
<div class="card">
    <div class="card-header-clean">
        <h3 class="card-title">Pengumuman Penting untuk Anda</h3>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-sm">Pusat Pengumuman</a>
    </div>
    <div>
        @forelse ($recentPengumuman as $p)
            <div style="padding: 10px 0; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                <div>
                    <a href="{{ route('pengumuman.show', $p->id_pengumuman) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none; font-size: 13.5px;">
                        {{ $p->judul }}
                    </a>
                    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                        {{ $p->kategori }} &bull; {{ $p->tanggal ? $p->tanggal->translatedFormat('d M Y') : '-' }} &bull; Oleh: {{ $p->user->nama ?? 'Pengurus' }}
                    </div>
                </div>
                <span class="badge" style="background-color: var(--primary-light); color: var(--primary);">{{ $p->kategori }}</span>
            </div>
        @empty
            <div style="color: var(--text-muted); font-size: 13px; padding: 12px 0;">Belum ada pengumuman saat ini.</div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctxAbs = document.getElementById('siswaChartAbsensi');
    if (ctxAbs) {
        new Chart(ctxAbs, {
            type: 'doughnut',
            data: {
                labels: ['Hadir', 'Izin', 'Sakit', 'Alpa'],
                datasets: [{
                    data: [
                        {{ $chartAbsensi['Hadir'] }},
                        {{ $chartAbsensi['Izin'] }},
                        {{ $chartAbsensi['Sakit'] }},
                        {{ $chartAbsensi['Alpa'] }}
                    ],
                    backgroundColor: ['#315C49', '#3C7A89', '#E8A66A', '#C25953'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const ctxPkt = document.getElementById('siswaChartPiket');
    if (ctxPkt) {
        new Chart(ctxPkt, {
            type: 'pie',
            data: {
                labels: ['Selesai', 'Belum'],
                datasets: [{
                    data: [{{ $chartPiket['Selesai'] }}, {{ $chartPiket['Belum'] }}],
                    backgroundColor: ['#315C49', '#E8A66A'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
@endsection
