@extends('layouts.dashboard')

@section('title', $pengumuman->judul . ' - Cibedug 2 Management')

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
    <div>
        <a href="{{ route('pengumuman.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #64748b; text-decoration: none; margin-bottom: 8px;">
            &larr; Kembali ke Daftar Pengumuman
        </a>
        <h1>{{ $pengumuman->judul }}</h1>
    </div>

    @if (!Auth::user()->isUser())
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('pengumuman.edit', $pengumuman->id_pengumuman) }}" class="btn btn-warning-sm">Ubah</a>
            <form action="{{ route('pengumuman.destroy', $pengumuman->id_pengumuman) }}" method="POST" style="display: inline;" onsubmit="return confirm('Hapus pengumuman ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger-sm">Hapus</button>
            </form>
        </div>
    @endif
</div>

@php
    $kategoriColor = match($pengumuman->kategori) {
        'Penting' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'border' => '#f87171'],
        'Akademik' => ['bg' => '#e0e7ff', 'color' => '#3730a3', 'border' => '#818cf8'],
        'Kegiatan' => ['bg' => '#fef3c7', 'color' => '#92400e', 'border' => '#fcd34d'],
        default => ['bg' => '#e2e8f0', 'color' => '#334155', 'border' => '#cbd5e1'],
    };
@endphp

<div class="card" style="border-top: 4px solid {{ $kategoriColor['border'] }}; max-width: 840px;">
    <div class="card-header" style="background-color: #f8fafc; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="display: flex; gap: 8px; align-items: center;">
            <span class="badge-status" style="background: {{ $kategoriColor['bg'] }}; color: {{ $kategoriColor['color'] }}; font-weight: 600;">
                {{ $pengumuman->kategori }}
            </span>
            <span class="badge-status" style="background: #e2e8f0; color: #475569;">
                Target: {{ $pengumuman->target }}
            </span>
        </div>

        <div style="font-size: 12px; color: #64748b;">
            Dipublikasikan: <strong>{{ $pengumuman->tanggal ? $pengumuman->tanggal->format('d F Y') : '-' }}</strong>
            oleh <strong>{{ $pengumuman->user->nama ?? 'Pengurus' }}</strong> ({{ ucfirst($pengumuman->user->role ?? 'Admin') }})
        </div>
    </div>

    <div class="card-body" style="padding: 28px; font-size: 14.5px; line-height: 1.8; color: #1e293b; white-space: pre-line;">
        {{ $pengumuman->isi }}
    </div>

    <div style="padding: 16px 28px; background-color: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 11.5px; color: #94a3b8;">
            Cibedug 2 Management &bull; Pusat Informasi & Komunikasi
        </span>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-outline btn-sm">
            Kembali
        </a>
    </div>
</div>
@endsection
