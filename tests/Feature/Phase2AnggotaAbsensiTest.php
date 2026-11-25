<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase2AnggotaAbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $guru;
    protected User $siswa;
    protected Anggota $anggotaSiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin.test@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $this->guru = User::create([
            'nama' => 'Guru Test',
            'email' => 'guru.test@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        $this->siswa = User::create([
            'nama' => 'Ahmad Fauzi',
            'email' => 'ahmad@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $this->anggotaSiswa = Anggota::create([
            'id_users' => $this->siswa->id_users,
            'nis' => '1024501',
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'no_hp' => '08123456789',
        ]);
    }

    /**
     * Test Admin dan Guru dapat mengakses daftar anggota.
     */
    public function test_admin_and_guru_can_view_anggota_index(): void
    {
        $response = $this->actingAs($this->admin)->get('/anggota');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('1024501');

        $response = $this->actingAs($this->guru)->get('/anggota');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
    }

    /**
     * Test Siswa (User) tidak dapat mengakses manajemen anggota.
     */
    public function test_siswa_cannot_access_anggota_index(): void
    {
        $response = $this->actingAs($this->siswa)->get('/anggota');
        $response->assertStatus(403);
    }

    /**
     * Test Admin dapat menambah anggota siswa baru beserta akun user-nya.
     */
    public function test_admin_can_create_anggota_with_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/anggota', [
            'nama' => 'Budi Setiawan',
            'email' => 'budi.setiawan@smkcibedug2.sch.id',
            'password' => 'secret12345',
            'nis' => '1024502',
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'no_hp' => '08219876543',
        ]);

        $response->assertRedirect(route('anggota.index'));
        $response->assertSessionHas('success');

        $createdUser = User::where('email', 'budi.setiawan@smkcibedug2.sch.id')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('user', $createdUser->role);

        $createdAnggota = Anggota::where('nis', '1024502')->first();
        $this->assertNotNull($createdAnggota);
        $this->assertEquals($createdUser->id_users, $createdAnggota->id_users);
    }

    /**
     * Test validasi unik pada NIS anggota.
     */
    public function test_anggota_validation_duplicate_nis_fails(): void
    {
        $response = $this->actingAs($this->admin)->post('/anggota', [
            'nama' => 'Siswa Duplikat',
            'email' => 'duplikat@smkcibedug2.sch.id',
            'nis' => '1024501', // NIS sudah ada
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
        ]);

        $response->assertSessionHasErrors(['nis']);
    }

    /**
     * Test Admin dapat mengubah data anggota siswa dan user.
     */
    public function test_admin_can_update_anggota(): void
    {
        $response = $this->actingAs($this->admin)->put("/anggota/{$this->anggotaSiswa->id_anggota}", [
            'nama' => 'Ahmad Fauzi Updated',
            'email' => 'ahmad.updated@smkcibedug2.sch.id',
            'nis' => '1024501',
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'no_hp' => '08999999999',
        ]);

        $response->assertRedirect(route('anggota.index'));

        $this->assertEquals('Ahmad Fauzi Updated', $this->siswa->fresh()->nama);
        $this->assertEquals('08999999999', $this->anggotaSiswa->fresh()->no_hp);
    }

    /**
     * Test Admin dapat menghapus anggota siswa beserta user terkait.
     */
    public function test_admin_can_delete_anggota_and_user(): void
    {
        $userId = $this->siswa->id_users;
        $anggotaId = $this->anggotaSiswa->id_anggota;

        $response = $this->actingAs($this->admin)->delete("/anggota/{$anggotaId}");
        $response->assertRedirect(route('anggota.index'));

        $this->assertNull(Anggota::find($anggotaId));
        $this->assertNull(User::find($userId));
    }

    /**
     * Test Guru tidak memiliki izin menambah, mengubah, atau menghapus anggota.
     */
    public function test_guru_cannot_create_or_delete_anggota(): void
    {
        $response = $this->actingAs($this->guru)->post('/anggota', [
            'nama' => 'Siswa Ilegal',
            'email' => 'ilegal@smkcibedug2.sch.id',
            'nis' => '9999999',
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'RPL',
        ]);
        $response->assertStatus(403);

        $response = $this->actingAs($this->guru)->delete("/anggota/{$this->anggotaSiswa->id_anggota}");
        $response->assertStatus(403);
    }

    /**
     * Test pencarian anggota berdasarkan nama dan NIS.
     */
    public function test_search_anggota(): void
    {
        $response = $this->actingAs($this->admin)->get('/anggota?q=1024501');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');

        $response = $this->actingAs($this->admin)->get('/anggota?q=NamaTidakAda999');
        $response->assertStatus(200);
        $response->assertSee('Tidak ada data anggota siswa');
    }

    /**
     * Test Admin dan Guru dapat mencatat absensi siswa.
     */
    public function test_admin_and_guru_can_record_absensi(): void
    {
        $response = $this->actingAs($this->guru)->post('/absensi', [
            'id_anggota' => $this->anggotaSiswa->id_anggota,
            'tanggal' => '2026-11-24',
            'status' => 'Hadir',
            'keterangan' => 'Tepat waktu',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $absensi = Absensi::where('id_anggota', $this->anggotaSiswa->id_anggota)->first();
        $this->assertNotNull($absensi);
        $this->assertEquals('Hadir', $absensi->status);
        $this->assertEquals('Tepat waktu', $absensi->keterangan);
    }

    /**
     * Test validasi status presensi (harus Hadir, Izin, Sakit, atau Alpa).
     */
    public function test_absensi_validation_invalid_status_fails(): void
    {
        $response = $this->actingAs($this->guru)->post('/absensi', [
            'id_anggota' => $this->anggotaSiswa->id_anggota,
            'tanggal' => '2026-11-24',
            'status' => 'Bolos', // Status tidak valid
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * Test ubah dan hapus absensi oleh Guru/Admin.
     */
    public function test_admin_and_guru_can_update_and_delete_absensi(): void
    {
        $absensi = Absensi::create([
            'id_anggota' => $this->anggotaSiswa->id_anggota,
            'tanggal' => '2026-11-24',
            'status' => 'Hadir',
        ]);

        // Ubah absensi
        $response = $this->actingAs($this->admin)->put("/absensi/{$absensi->id_absensi}", [
            'id_anggota' => $this->anggotaSiswa->id_anggota,
            'tanggal' => '2026-11-24',
            'status' => 'Sakit',
            'keterangan' => 'Surat dokter',
        ]);
        $response->assertRedirect();
        $this->assertEquals('Sakit', $absensi->fresh()->status);

        // Hapus absensi
        $response = $this->actingAs($this->admin)->delete("/absensi/{$absensi->id_absensi}");
        $response->assertRedirect();
        $this->assertNull(Absensi::find($absensi->id_absensi));
    }

    /**
     * Test Siswa hanya dapat melihat riwayat absensinya sendiri dan tidak bisa input absensi.
     */
    public function test_siswa_can_view_own_absensi_and_cannot_create(): void
    {
        Absensi::create([
            'id_anggota' => $this->anggotaSiswa->id_anggota,
            'tanggal' => '2026-11-24',
            'status' => 'Hadir',
            'keterangan' => 'Hadir tepat waktu',
        ]);

        // Siswa buka /absensi
        $response = $this->actingAs($this->siswa)->get('/absensi');
        $response->assertStatus(200);
        $response->assertSee('Riwayat Kehadiran Siswa');
        $response->assertSee('Hadir tepat waktu');

        // Siswa coba POST ke /absensi -> Ditolak (403)
        $response = $this->actingAs($this->siswa)->post('/absensi', [
            'id_anggota' => $this->anggotaSiswa->id_anggota,
            'tanggal' => '2026-11-25',
            'status' => 'Hadir',
        ]);
        $response->assertStatus(403);
    }

    /**
     * Test relasi database Eloquent: User -> Anggota -> Absensi.
     */
    public function test_database_relations(): void
    {
        $absensi = Absensi::create([
            'id_anggota' => $this->anggotaSiswa->id_anggota,
            'tanggal' => '2026-11-24',
            'status' => 'Izin',
            'keterangan' => 'Acara keluarga',
        ]);

        // Relasi Anggota -> User & Absensi
        $this->assertEquals($this->siswa->nama, $this->anggotaSiswa->user->nama);
        $this->assertCount(1, $this->anggotaSiswa->absensi);

        // Relasi Absensi -> Anggota -> User
        $this->assertEquals($this->anggotaSiswa->nis, $absensi->anggota->nis);
        $this->assertEquals($this->siswa->nama, $absensi->anggota->user->nama);
    }
}
