@extends('layouts.dashboard')

@section('title', 'Dashboard Guru Pembimbing')

@section('content')
<!-- Hero Foto Kebersamaan Rayon -->
@include('components.hero_rayon')

<div class="card-header-clean">
    <div>
        <h2 class="card-title">Pemantauan Kesiswaan Rayon Cibedug 2</h2>
        <p class="card-subtitle">Pantau presensi harian, jadwal piket kebersihan, dan bimbingan anggota rayon.</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('absensi.index') }}" class="btn btn-primary btn-sm">+ Catat Presensi</a>
        <a href="{{ route('piket.create') }}" class="btn btn-outline btn-sm">+ Jadwal Piket</a>
        <a href="{{ route('pengumuman.create') }}" class="btn btn-outline btn-sm">+ Buat Pengumuman</a>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline btn-sm">Laporan Terpadu</a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Anggota Rayon</span>
            <div class="stat-icon green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $totalSiswa }}</div>
        <div class="stat-desc">Siswa bimbingan aktif</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Kehadiran Hari Ini</span>
            <div class="stat-icon green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--primary);">{{ $hadirToday }} / {{ $totalSiswa }}</div>
        <div class="stat-desc">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Petugas Piket Hari Ini</span>
            <div class="stat-icon teal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--info);">{{ $piketToday->count() }} Petugas</div>
        <div class="stat-desc">{{ $piketToday->where('status', 'Selesai')->count() }} telah menyelesaikan piket</div>
    </div>
</div>

<!-- Visualisasi Statistik Guru -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Rekap Kehadiran Anggota Rayon</h3>
        </div>
        <div style="height: 200px; position: relative;">
            <canvas id="guruChartKehadiran"></canvas>
        </div>
    </div>

    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Tingkat Penyelesaian Piket Kebersihan</h3>
        </div>
        <div style="height: 200px; position: relative;">
            <canvas id="guruChartPiket"></canvas>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px; margin-bottom: 24px;">
    <!-- Daftar Presensi Hari Ini -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Presensi Hari Ini ({{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }})</h3>
            <a href="{{ route('absensi.index') }}" class="btn btn-outline btn-sm">Jurnal Absensi</a>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Siswa</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensiToday as $abs)
                        <tr>
                            <td style="font-weight: 600;">{{ $abs->anggota?->user?->nama ?? '-' }}</td>
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
                            <td style="text-align: center;">
                                <a href="{{ route('absensi.edit', $abs->id_absensi) }}" class="btn btn-warning-sm btn-sm">Ubah</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Belum ada presensi yang diinput untuk hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Petugas Piket Hari Ini -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Petugas Piket Hari Ini</h3>
            <a href="{{ route('piket.index') }}" class="btn btn-outline btn-sm">Kelola Piket</a>
        </div>

        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Petugas</th>
                        <th>Tugas</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($piketToday as $pkt)
                        <tr>
                            <td style="font-weight: 600;">{{ $pkt->anggota?->user?->nama ?? '-' }}</td>
                            <td style="font-size: 13px;">{{ $pkt->tugas }}</td>
                            <td>
                                @if ($pkt->status === 'Selesai')
                                    <span class="badge badge-selesai">Selesai</span>
                                @else
                                    <span class="badge badge-belum">Belum</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                Belum ada jadwal piket yang ditetapkan untuk hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pengumuman untuk Guru -->
<div class="card">
    <div class="card-header-clean">
        <h3 class="card-title">Pengumuman & Warta Rayon Terkini</h3>
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
                        {{ $p->kategori }} &bull; Sasaran: {{ $p->target }} &bull; {{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }}
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
    const ctxKehadiran = document.getElementById('guruChartKehadiran');
    if (ctxKehadiran) {
        new Chart(ctxKehadiran, {
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

    const ctxPiket = document.getElementById('guruChartPiket');
    if (ctxPiket) {
        new Chart(ctxPiket, {
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
