@extends('layouts.dashboard')

@section('title', 'Pusat Pengumuman Siswa - Cibedug 2 Management')

@section('content')
<div class="page-header">
    <div>
        <h1>Papan Informasi & Pengumuman</h1>
        <p>Pengumuman dan pemberitahuan penting untuk seluruh siswa Rayon Cibedug 2.</p>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 16px;">
        <form method="GET" action="{{ route('pengumuman.index') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <input 
                type="text" 
                name="q" 
                value="{{ request('q') }}" 
                class="form-control" 
                style="flex: 1; min-width: 200px; height: 36px; font-size: 13px;" 
                placeholder="Cari pengumuman..."
            >

            <select name="kategori" class="form-control" style="width: 160px; height: 36px; font-size: 13px;" onchange="this.form.submit()">
                <option value="all">Semua Kategori</option>
                <option value="Informasi" {{ request('kategori') === 'Informasi' ? 'selected' : '' }}>Informasi</option>
                <option value="Kegiatan" {{ request('kategori') === 'Kegiatan' ? 'selected' : '' }}>Kegiatan</option>
                <option value="Akademik" {{ request('kategori') === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                <option value="Penting" {{ request('kategori') === 'Penting' ? 'selected' : '' }}>Penting</option>
            </select>

            <button type="submit" class="btn btn-outline btn-sm">Cari</button>
            @if(request('q') || (request('kategori') && request('kategori') !== 'all'))
                <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-sm" style="color: #64748b;">Reset</a>
            @endif
        </form>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
    @forelse ($pengumumanList as $p)
        @php
            $kategoriColor = match($p->kategori) {
                'Penting' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#f87171'],
                'Akademik' => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'border' => '#818cf8'],
                'Kegiatan' => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fcd34d'],
                default => ['bg' => '#e2e8f0', 'color' => '#334155', 'border' => '#cbd5e1'],
            };
        @endphp
        <div class="card" style="display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid {{ $kategoriColor['border'] }};">
            <div class="card-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <span class="badge-status" style="background: {{ $kategoriColor['bg'] }}; color: {{ $kategoriColor['color'] }}; font-weight: 600;">
                        {{ $p->kategori }}
                    </span>
                    <span style="font-size: 11.5px; color: #64748b;">
                        {{ $p->tanggal ? $p->tanggal->format('d M Y') : '-' }}
                    </span>
                </div>

                <h3 style="font-size: 16px; margin: 0 0 10px 0; color: #002b49;">
                    <a href="{{ route('pengumuman.show', $p->id_pengumuman) }}" style="color: inherit; text-decoration: none;">
                        {{ $p->judul }}
                    </a>
                </h3>

                <p style="font-size: 13px; color: #475569; line-height: 1.5; margin-bottom: 16px;">
                    {{ Str::limit(strip_tags($p->isi), 110) }}
                </p>
            </div>

            <div style="padding: 12px 20px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; color: #64748b;">
                    Oleh: <strong>{{ $p->user->nama ?? 'Pengurus' }}</strong>
                </span>
                <a href="{{ route('pengumuman.show', $p->id_pengumuman) }}" class="btn btn-outline btn-sm" style="font-size: 12px;">
                    Baca Detail &rarr;
                </a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; background: #ffffff; padding: 40px; text-align: center; border-radius: 12px; border: 1px solid #e2e8f0; color: #94a3b8;">
            Belum ada pengumuman yang ditujukan kepada siswa saat ini.
        </div>
    @endforelse
</div>

@if ($pengumumanList->hasPages())
    <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
        {{ $pengumumanList->links() }}
    </div>
@endif
@endsection
