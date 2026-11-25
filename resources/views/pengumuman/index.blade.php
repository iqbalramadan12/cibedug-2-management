@extends('layouts.dashboard')

@section('title', 'Pusat Informasi & Pengumuman - Cibedug 2 Management')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1>Pusat Informasi & Pengumuman</h1>
        <p>Distribusi informasi kegiatan, akademik, dan warta penting rayon Cibedug 2.</p>
    </div>

    <a href="{{ route('pengumuman.create') }}" class="btn btn-primary">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Buat Pengumuman Baru</span>
    </a>
</div>

<div class="card">
    <div class="card-header" style="display: flex; flex-direction: column; gap: 14px; align-items: stretch;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <h2>Daftar Pengumuman Rayon</h2>
            <span style="font-size: 13px; color: #64748b;">Total: <strong>{{ $pengumumanList->total() }}</strong> warta</span>
        </div>

        <!-- Filter & Search -->
        <form method="GET" action="{{ route('pengumuman.index') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background-color: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}" 
                class="form-control" 
                style="width: 220px; height: 34px; font-size: 12.5px;" 
                placeholder="Cari judul / isi warta..."
            >

            <select name="kategori" class="form-control" style="width: 150px; height: 34px; font-size: 12.5px;">
                <option value="all">Semua Kategori</option>
                <option value="Informasi" {{ request('kategori') === 'Informasi' ? 'selected' : '' }}>Informasi</option>
                <option value="Kegiatan" {{ request('kategori') === 'Kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                <option value="Akademik" {{ request('kategori') === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                <option value="Penting" {{ request('kategori') === 'Penting' ? 'selected' : '' }}>Penting</option>
            </select>

            <select name="target" class="form-control" style="width: 140px; height: 34px; font-size: 12.5px;">
                <option value="all">Semua Sasaran</option>
                <option value="Semua" {{ request('target') === 'Semua' ? 'selected' : '' }}>Semua</option>
                <option value="Guru" {{ request('target') === 'Guru' ? 'selected' : '' }}>Guru</option>
                <option value="Siswa" {{ request('target') === 'Siswa' ? 'selected' : '' }}>Siswa</option>
            </select>

            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
            <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-sm" style="color: #64748b;">Reset</a>
        </form>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Judul Pengumuman</th>
                    <th>Kategori</th>
                    <th>Sasaran</th>
                    <th>Penulis</th>
                    <th>Tanggal</th>
                    <th style="text-align: center; width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengumumanList as $index => $p)
                    @php
                        $kategoriColor = match($p->kategori) {
                            'Penting' => ['bg' => '#fee2e2', 'color' => '#991b1b'],
                            'Akademik' => ['bg' => '#e0e7ff', 'color' => '#3730a3'],
                            'Kegiatan' => ['bg' => '#fef3c7', 'color' => '#92400e'],
                            default => ['bg' => '#e2e8f0', 'color' => '#334155'],
                        };
                    @endphp
                    <tr>
                        <td style="color: #64748b;">{{ $pengumumanList->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('pengumuman.show', $p->id_pengumuman) }}" style="font-weight: 600; color: #002b49; text-decoration: none;">
                                {{ $p->judul }}
                            </a>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                {{ Str::limit(strip_tags($p->isi), 70) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge-status" style="background: {{ $kategoriColor['bg'] }}; color: {{ $kategoriColor['color'] }}; font-weight: 600;">
                                {{ $p->kategori }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-status" style="background: #f1f5f9; color: #475569;">
                                {{ $p->target }}
                            </span>
                        </td>
                        <td>
                            <strong style="color: #0f172a;">{{ $p->user->nama ?? 'Sistem' }}</strong>
                            <div style="font-size: 11px; color: #64748b;">{{ ucfirst($p->user->role ?? '-') }}</div>
                        </td>
                        <td>{{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }}</td>
                        <td style="text-align: center; white-space: nowrap;">
                            <a href="{{ route('pengumuman.show', $p->id_pengumuman) }}" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 12px;">Lihat</a>
                            <a href="{{ route('pengumuman.edit', $p->id_pengumuman) }}" class="btn btn-warning-sm btn-sm">Ubah</a>
                            <form action="{{ route('pengumuman.destroy', $p->id_pengumuman) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus pengumuman ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger-sm btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #94a3b8; padding: 32px;">
                            Belum ada warta pengumuman yang sesuai kriteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($pengumumanList->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
            {{ $pengumumanList->links() }}
        </div>
    @endif
</div>
@endsection
