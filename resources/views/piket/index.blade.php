@extends('layouts.dashboard')

@section('title', 'Jadwal & Tugas Piket Kelas - Cibedug 2 Management')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1>Jadwal & Tugas Piket</h1>
        <p>Pantauan harian kebersihan dan piket Rayon Cibedug 2 SMK Wikrama Bogor untuk menciptakan iklim belajar kondusif.</p>
    </div>

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('piket.export.excel', request()->all()) }}" class="btn btn-outline btn-sm" style="background: #10b981; color: white; border-color: #10b981;">
            <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Export Excel
        </a>
        <a href="{{ route('piket.export.pdf', request()->all()) }}" class="btn btn-outline btn-sm" style="background: #ef4444; color: white; border-color: #ef4444;">
            <svg style="width: 14px; height: 14px; display: inline-block; vertical-align: middle; margin-right: 4px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Export PDF
        </a>
        <a href="{{ route('piket.create') }}" class="btn btn-primary">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Tambah Jadwal Piket</span>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="display: flex; flex-direction: column; gap: 14px; align-items: stretch;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h2>Daftar Petugas & Jadwal Piket</h2>
            <span style="font-size: 13px; color: #64748b;">Total: <strong>{{ $piketList->count() }}</strong> Penugasan</span>
        </div>

        <!-- Filter, Pencarian, & Pengurutan (Rubrik Search, Filter, Sort) -->
        <form method="GET" action="{{ route('piket.index') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background-color: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <!-- Pencarian Siswa -->
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}" 
                class="form-control" 
                style="width: 180px; height: 34px; font-size: 12.5px;" 
                placeholder="Cari nama siswa..."
            >

            <!-- Filter Tanggal -->
            <input 
                type="date" 
                name="tanggal" 
                value="{{ request('tanggal') && request('tanggal') !== 'all' ? request('tanggal') : '' }}" 
                class="form-control" 
                style="width: 140px; height: 34px; font-size: 12.5px;"
                title="Filter Tanggal"
            >

            <!-- Filter Status -->
            <select name="status" class="form-control" style="width: 130px; height: 34px; font-size: 12.5px;">
                <option value="all">Semua Status</option>
                <option value="Belum" {{ request('status') === 'Belum' ? 'selected' : '' }}>Belum</option>
                <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
            </select>

            <!-- Pengurutan (Sorting) -->
            <select name="sort_by" class="form-control" style="width: 140px; height: 34px; font-size: 12.5px;">
                <option value="tanggal" {{ request('sort_by') === 'tanggal' ? 'selected' : '' }}>Urut Tanggal</option>
                <option value="nama" {{ request('sort_by') === 'nama' ? 'selected' : '' }}>Urut Nama Siswa</option>
            </select>

            <select name="sort_order" class="form-control" style="width: 110px; height: 34px; font-size: 12.5px;">
                <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Terbaru / Z-A</option>
                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Terlama / A-Z</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm" style="height: 34px;">Terapkan</button>
            <a href="{{ route('piket.index') }}" class="btn btn-outline btn-sm" style="height: 34px; line-height: 24px; color: #64748b;">Reset</a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Nama Petugas</th>
                    <th>Hari / Tanggal</th>
                    <th>Tugas Kebersihan</th>
                    <th>Status</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($piketList as $piket)
                    <tr>
                        <td>
                            <strong style="color: #0f172a;">{{ $piket->anggota?->user?->nama ?? 'Siswa Tidak Ditemukan' }}</strong>
                            <div style="font-size: 11.5px; color: #64748b;">NIS: {{ $piket->anggota?->nis ?: '-' }} | Kelas: {{ $piket->anggota?->kelas ?: '-' }}</div>
                        </td>
                        <td style="font-weight: 500;">
                            {{ $piket->tanggal->translatedFormat('l, d M Y') }}
                        </td>
                        <td>
                            <span style="color: #334155;">{{ $piket->tugas }}</span>
                        </td>
                        <td>
                            <form action="{{ route('piket.updateStatus', $piket->id_piket) }}" method="POST" style="display: inline;">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $piket->status === 'Selesai' ? 'Belum' : 'Selesai' }}">
                                <button type="submit" class="badge-status {{ strtolower($piket->status) }}" style="border: none; cursor: pointer;" title="Klik untuk mengubah status ke {{ $piket->status === 'Selesai' ? 'Belum' : 'Selesai' }}">
                                    {{ $piket->status }} &#x21c4;
                                </button>
                            </form>
                        </td>
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="{{ route('piket.edit', $piket->id_piket) }}" class="btn btn-warning-sm btn-sm">Ubah</a>
                            <form action="{{ route('piket.destroy', $piket->id_piket) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal piket ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-sm btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #94a3b8; padding: 36px;">
                            Tidak ada jadwal piket yang sesuai dengan kriteria filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
