@extends('layouts.auth')

@section('title', 'Pendaftaran Akun - Cibedug 2 Management')

@section('content')
<div class="auth-card">
    <div class="auth-header">
        <span class="brand-badge-auth">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
            <span>Rayon Cibedug 2</span>
        </span>
        <h1>Pendaftaran Akun Baru</h1>
        <p>Daftarkan akun anggota atau pembimbing Rayon Cibedug 2</p>
    </div>

    @if ($errors->any())
        <div class="alert-auth-danger">
            <span>Mohon periksa kembali formulir pendaftaran di bawah ini.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" autocomplete="off">
        @csrf

        <div class="form-group">
            <label for="nama" class="form-label">Nama Lengkap</label>
            <div class="input-wrapper">
                <input 
                    type="text" 
                    id="nama" 
                    name="nama" 
                    value="{{ old('nama') }}" 
                    class="form-input @error('nama') is-invalid @enderror" 
                    placeholder="contoh: Muhammad Rizky"
                    required 
                    autofocus
                >
            </div>
            @error('nama')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Akun</label>
            <div class="input-wrapper">
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    class="form-input @error('email') is-invalid @enderror" 
                    placeholder="contoh: rizky@smkwikrama.sch.id"
                    required
                >
            </div>
            @error('email')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Kata Sandi (Minimal 8 karakter)</label>
            <div class="input-wrapper">
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input @error('password') is-invalid @enderror" 
                    placeholder="Minimal 8 karakter"
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

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
            <div class="input-wrapper">
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="form-input" 
                    placeholder="Ulangi kata sandi"
                    required
                >
                <button type="button" onclick="togglePassword('password_confirmation', this)" title="Lihat password" aria-label="Lihat password" style="position: absolute; right: 12px; background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; align-items: center;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn-auth-submit">
            <span>Daftar Akun</span>
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
        </button>

        <div class="auth-footer">
            Sudah punya akun? <a href="{{ route('login') }}" class="auth-link">Masuk Sekarang</a>
        </div>

        <div class="auth-institution-tag">
            Rayon Cibedug 2 &bull; SMK Wikrama Bogor
        </div>
    </form>
</div>
@endsection
