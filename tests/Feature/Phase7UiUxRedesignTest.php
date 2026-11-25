<?php

namespace Tests\Feature;

use App\Models\Anggota;
use App\Models\Pengaturan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase7UiUxRedesignTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $guru;
    protected $siswaUser;
    protected $anggota;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::firstOrCreate(
            ['email' => 'admin@smkcibedug2.sch.id'],
            [
                'nama' => 'Administrator Rayon',
                'password' => bcrypt('password123'),
                'role' => 'admin',
            ]
        );

        $this->guru = User::firstOrCreate(
            ['email' => 'guru@smkcibedug2.sch.id'],
            [
                'nama' => 'Guru Pembimbing Rayon',
                'password' => bcrypt('password123'),
                'role' => 'guru',
            ]
        );

        $this->siswaUser = User::firstOrCreate(
            ['email' => 'siswa@smkcibedug2.sch.id'],
            [
                'nama' => 'Ahmad Fauzi',
                'password' => bcrypt('password123'),
                'role' => 'user',
            ]
        );

        $this->anggota = Anggota::firstOrCreate(
            ['id_users' => $this->siswaUser->id_users],
            [
                'nis' => '1024501',
                'kelas' => 'PPLG XI-2',
                'jurusan' => 'Pengembangan Perangkat Lunak dan Gim',
                'no_hp' => '081234567890',
            ]
        );
    }

    /**
     * Test identitas rayon dan institusi muncul dengan benar di layout dashboard.
     */
    public function test_layout_contains_rayon_cibedug_2_and_smk_wikrama_bogor(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Rayon Cibedug 2');
        $response->assertSee('SMK Wikrama Bogor');
        $response->assertSee('hero-rayon-banner');
    }

    /**
     * Test admin dapat mengakses halaman Pengaturan Rayon.
     */
    public function test_admin_can_access_pengaturan_rayon(): void
    {
        $response = $this->actingAs($this->admin)->get(route('pengaturan.index'));
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Rayon Cibedug 2');
        $response->assertSee('SMK Wikrama Bogor');
    }

    /**
     * Test guru tidak diizinkan mengakses halaman Pengaturan Rayon.
     */
    public function test_guru_cannot_access_pengaturan_rayon(): void
    {
        $response = $this->actingAs($this->guru)->get(route('pengaturan.index'));
        $response->assertStatus(403);
    }

    /**
     * Test pembaruan slogan dan upload foto hero kebersamaan oleh admin.
     */
    public function test_admin_can_update_slogan_and_hero_photo(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('hero_bersama.jpg', 1200, 675);

        $response = $this->actingAs($this->admin)->post(route('pengaturan.update'), [
            'slogan_rayon' => 'Kebersamaan membangun generasi beradab dan terampil.',
            'foto_hero' => $file,
        ]);

        $response->assertRedirect(route('pengaturan.index'));

        $this->assertEquals('Kebersamaan membangun generasi beradab dan terampil.', Pengaturan::get('slogan_rayon'));
        $path = Pengaturan::get('foto_hero');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    /**
     * Test admin dapat menghapus foto hero kebersamaan (kembali ke placeholder).
     */
    public function test_admin_can_destroy_hero_photo(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test_hero.jpg');
        $path = $file->store('hero', 'public');
        Pengaturan::set('foto_hero', $path);

        $response = $this->actingAs($this->admin)->delete(route('pengaturan.destroyFoto'));
        $response->assertRedirect(route('pengaturan.index'));

        $this->assertNull(Pengaturan::get('foto_hero'));
        Storage::disk('public')->assertMissing($path);
    }

    /**
     * Test Admin dan Guru dapat melihat halaman profil anggota detail.
     */
    public function test_admin_and_guru_can_view_anggota_profile(): void
    {
        $response = $this->actingAs($this->admin)->get(route('anggota.show', $this->anggota->id_anggota));
        $response->assertStatus(200);
        $response->assertSee($this->siswaUser->nama);
        $response->assertSee($this->anggota->nis);
        $response->assertSee('Tingkat Kehadiran');
        $response->assertSee('Riwayat Tugas Piket');

        $responseGuru = $this->actingAs($this->guru)->get(route('anggota.show', $this->anggota->id_anggota));
        $responseGuru->assertStatus(200);
        $responseGuru->assertSee($this->siswaUser->nama);
    }

    /**
     * Test Siswa dapat mengakses profil mandiri melalui /user/profil.
     */
    public function test_siswa_can_view_own_profile(): void
    {
        $response = $this->actingAs($this->siswaUser)->get(route('user.profil'));
        $response->assertStatus(200);
        $response->assertSee('Profil Siswa Saya');
        $response->assertSee($this->siswaUser->nama);
        $response->assertSee($this->anggota->nis);
    }

    /**
     * Test alur upload dan hapus foto profil anggota siswa.
     */
    public function test_anggota_photo_upload_and_delete(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('foto_siswa.jpg', 400, 400);

        // Update anggota dengan upload foto baru
        $response = $this->actingAs($this->admin)->put(route('anggota.update', $this->anggota->id_anggota), [
            'nama' => $this->siswaUser->nama,
            'email' => $this->siswaUser->email,
            'nis' => $this->anggota->nis,
            'kelas' => $this->anggota->kelas,
            'jurusan' => $this->anggota->jurusan,
            'no_hp' => $this->anggota->no_hp,
            'foto' => $file,
        ]);

        $response->assertRedirect(route('anggota.index'));

        $this->anggota->refresh();
        $this->assertNotNull($this->anggota->foto);
        Storage::disk('public')->assertExists($this->anggota->foto);

        $oldFoto = $this->anggota->foto;

        // Update anggota dengan mencentang hapus_foto
        $responseDeleteFoto = $this->actingAs($this->admin)->put(route('anggota.update', $this->anggota->id_anggota), [
            'nama' => $this->siswaUser->nama,
            'email' => $this->siswaUser->email,
            'nis' => $this->anggota->nis,
            'kelas' => $this->anggota->kelas,
            'jurusan' => $this->anggota->jurusan,
            'no_hp' => $this->anggota->no_hp,
            'hapus_foto' => 1,
        ]);

        $responseDeleteFoto->assertRedirect(route('anggota.index'));

        $this->anggota->refresh();
        $this->assertNull($this->anggota->foto);
        Storage::disk('public')->assertMissing($oldFoto);
    }
}
