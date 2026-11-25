@php
    $heroFoto = \App\Models\Pengaturan::get('foto_hero');
    $slogan = \App\Models\Pengaturan::get('slogan_rayon', 'Tempat tumbuh, belajar, dan membangun kebersamaan.');
    $totalAnggota = \App\Models\Anggota::count();
@endphp

<div class="hero-rayon-banner" style="{{ $heroFoto ? "background-image: url('" . asset('storage/' . $heroFoto) . "');" : 'background: linear-gradient(135deg, #1C3B2F 0%, #244C3D 55%, #315C49 100%);' }}">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div>
            <div class="hero-tag">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span>Rayon Cibedug 2 &bull; SMK Wikrama Bogor</span>
            </div>
            <h1 class="hero-title">Rayon Cibedug 2</h1>
            <p class="hero-subtitle">{{ $slogan }}</p>
        </div>

        <div class="hero-badges-wrapper">
            <div class="hero-stat-badge">
                <span class="hero-stat-number">{{ $totalAnggota }}</span>
                <span class="hero-stat-label">Anggota Rayon</span>
            </div>

            @if (Auth::user()->isAdmin())
                <a href="{{ route('pengaturan.index') }}" class="btn-hero-manage" title="Kelola foto dan informasi Rayon">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Kelola Hero</span>
                </a>
            @endif
        </div>
    </div>
</div>
