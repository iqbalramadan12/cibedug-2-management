@extends('layouts.dashboard')

@section('title', 'Dashboard Administrator')

@section('content')
<!-- Hero Foto Kebersamaan Rayon -->
@include('components.hero_rayon')

<div class="card-header-clean">
    <div>
        <h2 class="card-title">Ringkasan Sistem & Operasional</h2>
        <p class="card-subtitle">Pengawasan menyeluruh kesiswaan, presensi, kebersihan, dan keuangan kas Rayon Cibedug 2.</p>
    </div>
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('anggota.create') }}" class="btn btn-primary btn-sm">+ Tambah Siswa</a>
        <a href="{{ route('pengumuman.create') }}" class="btn btn-outline btn-sm">+ Buat Pengumuman</a>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline btn-sm">Laporan Terpadu</a>
    </div>
</div>

<!-- Stat Cards Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Total Anggota Rayon</span>
            <div class="stat-icon green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $totalSiswa }}</div>
        <div class="stat-desc">Siswa aktif terdaftar di Rayon Cibedug 2</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Guru Pembimbing</span>
            <div class="stat-icon sage">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="stat-value">{{ $totalGuru }}</div>
        <div class="stat-desc">Pembimbing rayon aktif</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Kehadiran Hari Ini</span>
            <div class="stat-icon green">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--primary);">{{ $hadirToday }}</div>
        <div class="stat-desc">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-header">
            <span class="stat-title">Tugas Piket Hari Ini</span>
            <div class="stat-icon teal">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="stat-value" style="color: var(--info);">{{ $piketTodayCount }}</div>
        <div class="stat-desc">{{ $totalPiket }} total riwayat penugasan</div>
    </div>
</div>

<!-- Visualisasi Statistik Chart.js -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px; margin-bottom: 24px;">
    <!-- 1. Grafik Kehadiran Siswa -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Statistik Kehadiran Anggota Rayon</h3>
        </div>
        <div style="height: 220px; position: relative;">
            <canvas id="chartKehadiran"></canvas>
        </div>
    </div>

    <!-- 2. Grafik Status Piket -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Statistik Status Piket Kebersihan</h3>
        </div>
        <div style="height: 220px; position: relative;">
            <canvas id="chartPiket"></canvas>
        </div>
    </div>

    <!-- 3. Grafik Keuangan Kas -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Arus Kas Rayon (Pemasukan vs Pengeluaran)</h3>
        </div>
        <div style="height: 220px; position: relative;">
            <canvas id="chartKas"></canvas>
        </div>
    </div>

    <!-- 4. Statistik Jumlah Anggota per Jurusan -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Distribusi Konsentrasi Keahlian Siswa</h3>
        </div>
        <div style="height: 220px; position: relative;">
            <canvas id="chartJurusan"></canvas>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px;">
    <!-- Modul Kontrol Cepat -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Tautan Cepat Modul Rayon</h3>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('anggota.index') }}" class="btn btn-outline btn-sm">Direktori Anggota</a>
            <a href="{{ route('absensi.index') }}" class="btn btn-outline btn-sm">Jurnal Absensi</a>
            <a href="{{ route('piket.index') }}" class="btn btn-outline btn-sm">Jadwal Piket</a>
            <a href="{{ route('kas.index') }}" class="btn btn-outline btn-sm">Kas Rayon</a>
            <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-sm">Pusat Pengumuman</a>
            <a href="{{ route('laporan.index') }}" class="btn btn-outline btn-sm">Laporan Terpadu</a>
            <a href="{{ route('pengaturan.index') }}" class="btn btn-secondary btn-sm">Pengaturan Rayon</a>
        </div>
    </div>

    <!-- Warta & Pengumuman Terkini -->
    <div class="card">
        <div class="card-header-clean">
            <h3 class="card-title">Pengumuman Terkini</h3>
            <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div>
            @forelse ($recentPengumuman as $p)
                <div style="padding: 10px 0; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                    <div>
                        <a href="{{ route('pengumuman.show', $p->id_pengumuman) }}" style="font-weight: 600; color: var(--text-main); text-decoration: none; font-size: 13.5px;">
                            {{ $p->judul }}
                        </a>
                        <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                            {{ $p->kategori }} &bull; Target: {{ $p->target }} &bull; {{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }}
                        </div>
                    </div>
                    <span class="badge" style="background-color: var(--primary-light); color: var(--primary);">{{ $p->kategori }}</span>
                </div>
            @empty
                <div style="color: var(--text-muted); font-size: 13px; padding: 12px 0;">Belum ada pengumuman yang dipublikasikan.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Chart Kehadiran
    const ctxKehadiran = document.getElementById('chartKehadiran');
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

    // 2. Chart Piket
    const ctxPiket = document.getElementById('chartPiket');
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

    // 3. Chart Kas
    const ctxKas = document.getElementById('chartKas');
    if (ctxKas) {
        new Chart(ctxKas, {
            type: 'bar',
            data: {
                labels: ['Pemasukan (Rp)', 'Pengeluaran (Rp)'],
                datasets: [{
                    label: 'Nominal Kas (Rp)',
                    data: [{{ $chartKas['Pemasukan'] }}, {{ $chartKas['Pengeluaran'] }}],
                    backgroundColor: ['#315C49', '#C25953'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#EEF1EC' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 4. Chart Jurusan
    const ctxJurusan = document.getElementById('chartJurusan');
    if (ctxJurusan) {
        new Chart(ctxJurusan, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_keys($chartJurusan)) !!},
                datasets: [{
                    label: 'Jumlah Siswa',
                    data: {!! json_encode(array_values($chartJurusan)) !!},
                    backgroundColor: '#244C3D',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#EEF1EC' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endsection
