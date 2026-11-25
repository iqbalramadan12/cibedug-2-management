@extends('layouts.dashboard')

@section('title', 'Riwayat Absensi Siswa - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Riwayat Kehadiran Siswa</h1>
        <p>Pantau rekapitulasi kehadiran dan kedisiplinan Anda selama di Rayon Cibedug 2.</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Hadir</div>
            <div class="stat-value" style="color: #16a34a;">{{ $stats['hadir'] }} Hari</div>
            <div class="stat-sub">Kehadiran aktif</div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Izin</div>
            <div class="stat-value" style="color: #ca8a04;">{{ $stats['izin'] }} Hari</div>
            <div class="stat-sub">Izin resmi</div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Sakit</div>
            <div class="stat-value" style="color: #4f46e5;">{{ $stats['sakit'] }} Hari</div>
            <div class="stat-sub">Keterangan sakit</div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Alpa / Tanpa Ket.</div>
            <div class="stat-value" style="color: #dc2626;">{{ $stats['alpa'] }} Hari</div>
            <div class="stat-sub">Persentase: {{ $stats['persentase'] }}%</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Riwayat Seluruh Presensi Anda</h2>
        <span style="font-size: 13px; color: #64748b;">Total: <strong>{{ $riwayatAbsensi->count() }}</strong> Catatan</span>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Status Kehadiran</th>
                    <th>Keterangan / Alasan</th>
                    <th>Waktu Pencatatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayatAbsensi as $absensi)
                    <tr>
                        <td style="font-weight: 600;">{{ $absensi->tanggal->format('d F Y') }}</td>
                        <td>
                            <span class="badge-status {{ strtolower($absensi->status) }}">
                                {{ $absensi->status }}
                            </span>
                        </td>
                        <td>{{ $absensi->keterangan ?: '-' }}</td>
                        <td style="color: #64748b; font-size: 12.5px;">{{ $absensi->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 32px;">
                            Belum ada riwayat catatan absensi untuk Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
