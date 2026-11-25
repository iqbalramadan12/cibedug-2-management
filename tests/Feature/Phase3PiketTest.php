<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Piket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Phase3PiketTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $guru;
    protected User $siswa1;
    protected User $siswa2;
    protected Anggota $anggota1;
    protected Anggota $anggota2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Akun Admin
        $this->admin = User::create([
            'nama' => 'Budi Santoso (Admin)',
            'email' => 'admin@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. Akun Guru
        $this->guru = User::create([
            'nama' => 'Sri Wahyuni, S.Pd.',
            'email' => 'guru@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        // 3. Siswa 1
        $this->siswa1 = User::create([
            'nama' => 'Ahmad Fauzi',
            'email' => 'ahmad@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        $this->anggota1 = Anggota::create([
            'id_users' => $this->siswa1->id_users,
            'nis' => '1024501',
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'no_hp' => '08123456789',
        ]);

        // 4. Siswa 2
        $this->siswa2 = User::create([
            'nama' => 'Budi Setiawan',
            'email' => 'budi@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        $this->anggota2 = Anggota::create([
            'id_users' => $this->siswa2->id_users,
            'nis' => '1024502',
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'no_hp' => '08219876543',
        ]);
    }

    /**
     * 1. Test Admin dapat mengelola jadwal piket (CRUD & status).
     */
    public function test_admin_can_manage_piket(): void
    {
        // View index
        $response = $this->actingAs($this->admin)->get('/piket');
        $response->assertStatus(200);
        $response->assertSee('Jadwal & Tugas Piket Kelas');

        // Create piket
        $response = $this->actingAs($this->admin)->post('/piket', [
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Menyapu lantai & membuang sampah',
            'status' => 'Belum',
        ]);
        $response->assertRedirect(route('piket.index'));
        $this->assertDatabaseHas('piket', [
            'id_anggota' => $this->anggota1->id_anggota,
            'tugas' => 'Menyapu lantai & membuang sampah',
            'status' => 'Belum',
        ]);

        $piket = Piket::first();

        // Update piket
        $response = $this->actingAs($this->admin)->put("/piket/{$piket->id_piket}", [
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Membersihkan kaca jendela',
            'status' => 'Selesai',
        ]);
        $response->assertRedirect(route('piket.index'));
        $this->assertEquals('Membersihkan kaca jendela', $piket->fresh()->tugas);
        $this->assertEquals('Selesai', $piket->fresh()->status);

        // Update status cepat
        $response = $this->actingAs($this->admin)->patch("/piket/{$piket->id_piket}/status", [
            'status' => 'Belum',
        ]);
        $response->assertRedirect();
        $this->assertEquals('Belum', $piket->fresh()->status);

        // Delete piket
        $response = $this->actingAs($this->admin)->delete("/piket/{$piket->id_piket}");
        $response->assertRedirect(route('piket.index'));
        $this->assertDatabaseMissing('piket', ['id_piket' => $piket->id_piket]);
    }

    /**
     * 2. Test Guru dapat mengelola jadwal piket.
     */
    public function test_guru_can_manage_piket(): void
    {
        $response = $this->actingAs($this->guru)->get('/piket');
        $response->assertStatus(200);

        $response = $this->actingAs($this->guru)->post('/piket', [
            'id_anggota' => $this->anggota2->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Merapikan meja guru & buku',
            'status' => 'Belum',
        ]);
        $response->assertRedirect(route('piket.index'));

        $piket = Piket::where('id_anggota', $this->anggota2->id_anggota)->first();
        $this->assertNotNull($piket);

        // Guru ubah status
        $response = $this->actingAs($this->guru)->patch("/piket/{$piket->id_piket}/status", [
            'status' => 'Selesai',
        ]);
        $response->assertRedirect();
        $this->assertEquals('Selesai', $piket->fresh()->status);
    }

    /**
     * 3. Test Siswa hanya melihat jadwal piket miliknya sendiri.
     */
    public function test_siswa_only_sees_own_piket(): void
    {
        // Piket untuk Siswa 1
        Piket::create([
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Tugas Khusus Siswa 1',
            'status' => 'Belum',
        ]);

        // Piket untuk Siswa 2
        Piket::create([
            'id_anggota' => $this->anggota2->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Tugas Rahasia Siswa 2',
            'status' => 'Selesai',
        ]);

        // Siswa 1 buka /piket
        $response = $this->actingAs($this->siswa1)->get('/piket');
        $response->assertStatus(200);
        $response->assertSee('Tugas Khusus Siswa 1');
        $response->assertDontSee('Tugas Rahasia Siswa 2');
    }

    /**
     * 4. Test Siswa tidak dapat melakukan CRUD piket.
     */
    public function test_siswa_cannot_perform_crud_on_piket(): void
    {
        $piket = Piket::create([
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Menyapu',
            'status' => 'Belum',
        ]);

        // Create -> 403
        $response = $this->actingAs($this->siswa1)->post('/piket', [
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-26',
            'tugas' => 'Tugas Ilegal',
            'status' => 'Belum',
        ]);
        $response->assertStatus(403);

        // Edit/Update -> 403
        $response = $this->actingAs($this->siswa1)->put("/piket/{$piket->id_piket}", [
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Diubah Siswa',
            'status' => 'Selesai',
        ]);
        $response->assertStatus(403);

        // Update status -> 403
        $response = $this->actingAs($this->siswa1)->patch("/piket/{$piket->id_piket}/status", [
            'status' => 'Selesai',
        ]);
        $response->assertStatus(403);

        // Delete -> 403
        $response = $this->actingAs($this->siswa1)->delete("/piket/{$piket->id_piket}");
        $response->assertStatus(403);
    }

    /**
     * 5. Test validasi status piket hanya menerima 'Selesai' atau 'Belum'.
     */
    public function test_piket_validation_status_rules(): void
    {
        $response = $this->actingAs($this->admin)->post('/piket', [
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Tugas uji validasi',
            'status' => 'Pending', // Status invalid
        ]);

        $response->assertSessionHasErrors(['status']);
    }

    /**
     * 6. Test relasi Piket dengan Anggota dan User berjalan baik.
     */
    public function test_piket_relations_and_eager_loading(): void
    {
        $piket = Piket::create([
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Pel lantai kelas',
            'status' => 'Selesai',
        ]);

        // belongsTo Anggota
        $this->assertEquals($this->anggota1->id_anggota, $piket->anggota->id_anggota);
        // Eager loaded name through User
        $this->assertEquals('Ahmad Fauzi', $piket->anggota->user->nama);

        // hasMany from Anggota
        $this->assertCount(1, $this->anggota1->fresh()->piket);
    }

    /**
     * 7. Test pencarian jadwal piket berdasarkan nama siswa.
     */
    public function test_search_piket_by_student_name(): void
    {
        Piket::create([
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Menyapu',
            'status' => 'Belum',
        ]);
        Piket::create([
            'id_anggota' => $this->anggota2->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Mengepel',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($this->admin)->get('/piket?q=Ahmad');
        $response->assertStatus(200);
        $response->assertSee('Ahmad Fauzi');
        $response->assertDontSee('Budi Setiawan');
    }

    /**
     * 8. Test filter tanggal jadwal piket.
     */
    public function test_filter_piket_by_date(): void
    {
        Piket::create([
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Tugas 25 Nov',
            'status' => 'Belum',
        ]);
        Piket::create([
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-30',
            'tugas' => 'Tugas 30 Nov',
            'status' => 'Belum',
        ]);

        $response = $this->actingAs($this->admin)->get('/piket?tanggal=2026-11-25');
        $response->assertStatus(200);
        $response->assertSee('Tugas 25 Nov');
        $response->assertDontSee('Tugas 30 Nov');
    }

    /**
     * 9. Test filter status jadwal piket (Selesai/Belum).
     */
    public function test_filter_piket_by_status(): void
    {
        Piket::create([
            'id_anggota' => $this->anggota1->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Tugas yang sudah beres',
            'status' => 'Selesai',
        ]);
        Piket::create([
            'id_anggota' => $this->anggota2->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Tugas yang masih pending',
            'status' => 'Belum',
        ]);

        // Filter status Selesai
        $response = $this->actingAs($this->admin)->get('/piket?status=Selesai');
        $response->assertStatus(200);
        $response->assertSee('Tugas yang sudah beres');
        $response->assertDontSee('Tugas yang masih pending');

        // Filter status Belum
        $response = $this->actingAs($this->admin)->get('/piket?status=Belum');
        $response->assertStatus(200);
        $response->assertSee('Tugas yang masih pending');
        $response->assertDontSee('Tugas yang sudah beres');
    }

    /**
     * 10. Test sorting data piket (berdasarkan tanggal dan nama).
     */
    public function test_sorting_piket(): void
    {
        Piket::create([
            'id_anggota' => $this->anggota1->id_anggota, // Ahmad Fauzi
            'tanggal' => '2026-11-20',
            'tugas' => 'Tugas Awal',
            'status' => 'Belum',
        ]);
        Piket::create([
            'id_anggota' => $this->anggota2->id_anggota, // Budi Setiawan
            'tanggal' => '2026-11-28',
            'tugas' => 'Tugas Akhir',
            'status' => 'Selesai',
        ]);

        // Sort by tanggal asc
        $response = $this->actingAs($this->admin)->get('/piket?sort_by=tanggal&sort_order=asc');
        $response->assertStatus(200);

        // Sort by nama asc
        $response = $this->actingAs($this->admin)->get('/piket?sort_by=nama&sort_order=asc');
        $response->assertStatus(200);
    }

    /**
     * 11. Test seluruh halaman dashboard role berjalan tanpa error.
     */
    public function test_dashboards_render_without_error(): void
    {
        $response = $this->actingAs($this->siswa1)->get('/user/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($this->guru)->get('/guru/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /**
     * 12. Test login Guru mengarahkan ke dashboard Guru (verifikasi audit login).
     */
    public function test_guru_login_redirects_to_guru_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'guru@smkcibedug2.sch.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('guru.dashboard'));
    }
}
