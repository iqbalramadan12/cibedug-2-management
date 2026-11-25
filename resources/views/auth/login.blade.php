@extends('layouts.auth')

@section('title', 'Sign In - Cibedug 2 Management')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <span class="brand-badge-auth">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span>SMK C2 &bull; Rayon Cibedug 2</span>
        </span>
        <h1>Cibedug 2 Management</h1>
        <p>Sistem Manajemen Kesiswaan, Presensi & Kas Rayon</p>
    </div>

    @if (session('success'))
        <div class="alert-auth-success">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-auth-danger">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" autocomplete="off">
        @csrf

        <div class="form-group">
            <label for="login" class="form-label">Email atau NIS / NISN</label>
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="login" 
                    name="login" 
                    value="{{ old('login') }}" 
                    class="form-input @error('login') is-invalid @enderror" 
                    placeholder="Masukkan email atau NIS/NISN"
                    required 
                    autofocus
                >
            </div>
            @error('login')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi (Password)</label>
            <div class="input-wrapper">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input @error('password') is-invalid @enderror" 
                    placeholder="••••••••"
                    required
                >
                <button type="button" onclick="togglePassword('password', this)" title="Lihat password" aria-label="Lihat password" style="position: absolute; right: 12px; background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn-auth-submit">
            <span>Masuk ke Akun</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>

        <div class="auth-footer">
            Belum punya akun? <a href="{{ route('register') }}" class="auth-link">Daftar Akun Baru</a>
        </div>

        <div class="auth-institution-tag">
            Rayon Cibedug 2 &bull; SMK Wikrama Bogor
        </div>
    </form>
</div>
@endsection
