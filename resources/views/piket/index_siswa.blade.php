@extends('layouts.dashboard')

@section('title', 'Jadwal Piket Saya - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Jadwal Piket Kelas Saya</h1>
        <p>Pantau tugas piket harian dan pastikan ruang kelas tetap bersih dan rapi.</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Penugasan</div>
            <div class="stat-value">{{ $stats['total'] }} Tugas</div>
            <div class="stat-sub">Jadwal tercatat</div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Tugas Selesai</div>
            <div class="stat-value" style="color: #16a34a;">{{ $stats['selesai'] }} Selesai</div>
            <div class="stat-sub">Telah dituntaskan</div>
        </div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Belum Selesai</div>
            <div class="stat-value" style="color: #ca8a04;">{{ $stats['belum'] }} Belum</div>
            <div class="stat-sub">Perlu dikerjakan</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Daftar Jadwal Piket Pribadi</h2>

        <!-- Filter Status & Tanggal untuk Siswa -->
        <form method="GET" action="{{ route('piket.index') }}" class="search-form" style="gap: 10px;">
            <select name="status" class="form-control" style="width: 130px; height: 34px; font-size: 12.5px;">
                <option value="all">Semua Status</option>
                <option value="Belum" {{ request('status') === 'Belum' ? 'selected' : '' }}>Belum</option>
                <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
            <input 
                type="date" 
                name="tanggal" 
                value="{{ request('tanggal') && request('tanggal') !== 'all' ? request('tanggal') : '' }}" 
                class="form-control" 
                style="width: 140px; height: 34px; font-size: 12.5px;"
            >
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            <a href="{{ route('piket.index') }}" class="btn btn-outline btn-sm" style="color: #64748b;">Reset</a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Hari / Tanggal</th>
                    <th>Tugas Kebersihan</th>
                    <th>Status</th>
                    <th>Waktu Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($piketList as $piket)
                    <tr>
                        <td style="font-weight: 600;">
                            {{ $piket->tanggal->translatedFormat('l, d F Y') }}
                        </td>
                        <td>{{ $piket->tugas }}</td>
                        <td>
                            <span class="badge-status {{ strtolower($piket->status) }}">
                                {{ $piket->status }}
                            </span>
                        </td>
                        <td style="color: #64748b; font-size: 12.5px;">
                            {{ $piket->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 32px;">
                            Belum ada jadwal piket yang ditugaskan kepada Anda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
