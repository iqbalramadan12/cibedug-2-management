<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test halaman login dan register dapat diakses.
     */
    public function test_auth_pages_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Cibedug 2 Management');
        $response->assertSee('SMK C2');

        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Akun Baru');
    }

    /**
     * Test login menggunakan Email.
     */
    public function test_login_with_email_success(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin@smkcibedug2.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('admin', Auth::user()->role);
    }

    /**
     * Test login menggunakan NISN.
     */
    public function test_login_with_nisn_success(): void
    {
        $response = $this->post('/login', [
            'login' => '1024501',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticated();
        $this->assertEquals('user', Auth::user()->role);
        $this->assertEquals('Ahmad Fauzi', Auth::user()->nama);
    }

    /**
     * Test registrasi akun baru (Role selalu user, password ter-hash).
     */
    public function test_register_creates_user_with_hashed_password(): void
    {
        $uniqueEmail = 'budi.baru' . time() . '@smkcibedug2.sch.id';

        $response = $this->post('/register', [
            'nama' => 'Budi Baru',
            'email' => $uniqueEmail,
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertEquals('user', $user->role);
        $this->assertTrue(Hash::check('secret12345', $user->password));
        $this->assertNotEquals('secret12345', $user->password); // Pastikan bukan plain text

        // Pastikan entri anggota awal dibuat
        $this->assertNotNull($user->anggota);
    }

    /**
     * Test registrasi validasi password minimal 8 karakter.
     */
    public function test_register_fails_with_short_password(): void
    {
        $response = $this->post('/register', [
            'nama' => 'User Gagal',
            'email' => 'gagal@smkcibedug2.sch.id',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /**
     * Test proteksi role permission: Siswa tidak bisa buka Admin atau Guru.
     */
    public function test_user_cannot_access_admin_or_guru_dashboard(): void
    {
        $siswa = User::where('email', 'siswa@smkcibedug2.sch.id')->first();

        // Coba akses Admin
        $response = $this->actingAs($siswa)->get('/admin/dashboard');
        $response->assertStatus(403);

        // Coba akses Guru
        $response = $this->actingAs($siswa)->get('/guru/dashboard');
        $response->assertStatus(403);

        // Akses dashboard miliknya sendiri berhasil
        $response = $this->actingAs($siswa)->get('/user/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Selamat Datang, Ahmad Fauzi');
    }

    /**
     * Test proteksi role permission: Guru tidak bisa buka Admin.
     */
    public function test_guru_cannot_access_admin_dashboard(): void
    {
        $guru = User::where('email', 'guru@smkcibedug2.sch.id')->first();

        $response = $this->actingAs($guru)->get('/admin/dashboard');
        $response->assertStatus(403);

        $response = $this->actingAs($guru)->get('/guru/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sri Wahyuni, S.Pd.');
    }

    /**
     * Test tamu yang belum login di-redirect saat akses halaman protected.
     */
    public function test_guest_redirected_to_login(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/user/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test logout berhasil dan membatalkan sesi.
     */
    public function test_logout_invalidates_session(): void
    {
        $admin = User::where('email', 'admin@smkcibedug2.sch.id')->first();

        $response = $this->actingAs($admin)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
