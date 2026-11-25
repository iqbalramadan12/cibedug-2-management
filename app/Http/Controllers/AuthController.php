<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Tampilkan form login (Sign-In)
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Proses login (Email atau NISN)
     */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email Sekolah atau NISN wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = trim($request->input('login'));
        $password = $request->input('password');
        $user = null;

        // 1. Cek berdasarkan format Email
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', $loginInput)->first();
        }

        // 2. Jika bukan email atau belum ditemukan, cari via NISN pada tabel anggota
        if (!$user) {
            $anggota = Anggota::where('nis', $loginInput)->first();
            if ($anggota) {
                $user = $anggota->user;
            }
        }

        // 3. Fallback jika user memasukkan email yang mungkin tidak lolos filter standar
        if (!$user) {
            $user = User::where('email', $loginInput)->first();
        }

        // 4. Verifikasi user dan password
        if (!$user || !Hash::check($password, $user->password)) {
            return back()->withErrors([
                'login' => 'Email/NISN atau Password yang Anda masukkan tidak sesuai.',
            ])->withInput($request->only('login'));
        }

        // 5. Login session
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return $this->redirectBasedOnRole($user);
    }

    /**
     * Tampilkan form registrasi akun baru
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.register');
    }

    /**
     * Proses registrasi akun baru (Hanya role user/siswa)
     */
    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email sekolah atau Gmail wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar dalam sistem.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Registrasi publik selalu role user (Guru dan Admin tidak bisa dibuat dari form ini)
        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
        ]);

        // Buat profil anggota awal untuk user siswa
        Anggota::create([
            'id_users' => $user->id_users,
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil didaftarkan! Silakan login untuk melanjutkan.');
    }

    /**
     * Proses logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil logout.');
    }

    /**
     * Helper redirect berdasarkan role
     */
    private function redirectBasedOnRole($user)
    {
        $role = strtolower(trim($user->role ?? ''));
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('guru.dashboard'),
            default => redirect()->route('user.dashboard'),
        };
    }
}
