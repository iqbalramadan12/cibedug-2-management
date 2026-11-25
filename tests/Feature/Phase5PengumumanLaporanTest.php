<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kas;
use App\Models\Pengumuman;
use App\Models\Piket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase5PengumumanLaporanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $guru;
    protected User $siswa;
    protected Anggota $anggota;

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

        // 3. Akun Siswa & Anggota
        $this->siswa = User::create([
            'nama' => 'Ahmad Fauzi',
            'email' => 'ahmad@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        $this->anggota = Anggota::create([
            'id_users' => $this->siswa->id_users,
            'nis' => '12100001',
            'kelas' => 'XI RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'no_hp' => '081234567890',
        ]);
    }

    /**
     * 1. Struktur Tabel Pengumuman
     */
    public function test_pengumuman_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('pengumuman'));
        $this->assertTrue(Schema::hasColumns('pengumuman', [
            'id_pengumuman',
            'id_users',
            'judul',
            'isi',
            'kategori',
            'target',
            'tanggal',
            'created_at',
            'updated_at',
        ]));
    }

    /**
     * 2. Relasi Eloquent Pengumuman dan User
     */
    public function test_pengumuman_belongs_to_user_and_user_has_many_pengumuman(): void
    {
        $pengumuman = Pengumuman::create([
            'id_users' => $this->guru->id_users,
            'judul' => 'Ulangan Harian Pemrograman Web',
            'isi' => 'Materi Laravel & MVC dilaksanakan hari Senin.',
            'kategori' => 'Akademik',
            'target' => 'Siswa',
            'tanggal' => '2026-11-25',
        ]);

        $this->assertInstanceOf(User::class, $pengumuman->user);
        $this->assertEquals($this->guru->id_users, $pengumuman->user->id_users);
        $this->assertTrue($this->guru->pengumuman->contains($pengumuman));
    }

    /**
     * 3. Akses Daftar Pengumuman: Admin & Guru Melihat Semua
     */
    public function test_admin_and_guru_can_view_all_pengumuman(): void
    {
        Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Rapat Dewan Guru Terbatas',
            'isi' => 'Pembahasan persiapan SAS ganjil.',
            'kategori' => 'Penting',
            'target' => 'Guru',
            'tanggal' => '2026-11-25',
        ]);

        Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Libur Semester Genap',
            'isi' => 'Libur dimulai tanggal 20 Desember.',
            'kategori' => 'Informasi',
            'target' => 'Semua',
            'tanggal' => '2026-11-26',
        ]);

        $resAdmin = $this->actingAs($this->admin)->get(route('pengumuman.index'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('Rapat Dewan Guru Terbatas');
        $resAdmin->assertSee('Libur Semester Genap');

        $resGuru = $this->actingAs($this->guru)->get(route('pengumuman.index'));
        $resGuru->assertStatus(200);
        $resGuru->assertSee('Rapat Dewan Guru Terbatas');
        $resGuru->assertSee('Libur Semester Genap');
    }

    /**
     * 4. Akses Daftar Pengumuman: Siswa Hanya Melihat Target Siswa dan Semua
     */
    public function test_siswa_can_only_view_pengumuman_for_siswa_and_semua(): void
    {
        Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Warta Khusus Guru',
            'isi' => 'Informasi tunjangan dan sertifikasi.',
            'kategori' => 'Informasi',
            'target' => 'Guru',
            'tanggal' => '2026-11-25',
        ]);

        Pengumuman::create([
            'id_users' => $this->guru->id_users,
            'judul' => 'Warta Siswa Terbuka',
            'isi' => 'Jadwal piket bersih kelas bersama.',
            'kategori' => 'Kegiatan',
            'target' => 'Siswa',
            'tanggal' => '2026-11-25',
        ]);

        $resSiswa = $this->actingAs($this->siswa)->get(route('pengumuman.index'));
        $resSiswa->assertStatus(200);
        $resSiswa->assertViewIs('pengumuman.index_siswa');
        $resSiswa->assertSee('Warta Siswa Terbuka');
        $resSiswa->assertDontSee('Warta Khusus Guru');
    }

    /**
     * 5. CRUD Pengumuman: Admin dan Guru Dapat Membuat Pengumuman
     */
    public function test_admin_and_guru_can_create_pengumuman(): void
    {
        $payload = [
            'judul' => 'Workshop PPLG Industri 2026',
            'isi' => 'Wajib dihadiri seluruh siswa jurusan Rekayasa Perangkat Lunak.',
            'kategori' => 'Kegiatan',
            'target' => 'Semua',
            'tanggal' => '2026-11-28',
        ];

        $response = $this->actingAs($this->admin)->post(route('pengumuman.store'), $payload);
        $response->assertRedirect(route('pengumuman.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pengumuman', [
            'id_users' => $this->admin->id_users,
            'judul' => 'Workshop PPLG Industri 2026',
            'kategori' => 'Kegiatan',
        ]);
    }

    /**
     * 6. Validasi Form Pengumuman
     */
    public function test_pengumuman_validation_rejects_invalid_data(): void
    {
        $invalidPayload = [
            'judul' => '',
            'isi' => '',
            'kategori' => 'KategoriPalsu',
            'target' => 'TargetPalsu',
            'tanggal' => 'bukan-tanggal',
        ];

        $response = $this->actingAs($this->admin)->post(route('pengumuman.store'), $invalidPayload);
        $response->assertSessionHasErrors(['judul', 'isi', 'kategori', 'target', 'tanggal']);
        $this->assertDatabaseCount('pengumuman', 0);
    }

    /**
     * 7. Update Pengumuman
     */
    public function test_admin_and_guru_can_update_pengumuman(): void
    {
        $p = Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Judul Awal',
            'isi' => 'Isi awal pengumuman.',
            'kategori' => 'Informasi',
            'target' => 'Semua',
            'tanggal' => '2026-11-20',
        ]);

        $updatePayload = [
            'judul' => 'Judul Diperbarui',
            'isi' => 'Isi telah direvisi.',
            'kategori' => 'Penting',
            'target' => 'Guru',
            'tanggal' => '2026-11-21',
        ];

        $response = $this->actingAs($this->guru)->put(route('pengumuman.update', $p->id_pengumuman), $updatePayload);
        $response->assertRedirect(route('pengumuman.index'));

        $this->assertDatabaseHas('pengumuman', [
            'id_pengumuman' => $p->id_pengumuman,
            'judul' => 'Judul Diperbarui',
            'kategori' => 'Penting',
            'target' => 'Guru',
        ]);
    }

    /**
     * 8. Hapus Pengumuman
     */
    public function test_admin_and_guru_can_delete_pengumuman(): void
    {
        $p = Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Pengumuman Dihapus',
            'isi' => 'Akan segera dihapus.',
            'kategori' => 'Informasi',
            'target' => 'Semua',
            'tanggal' => '2026-11-20',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('pengumuman.destroy', $p->id_pengumuman));
        $response->assertRedirect(route('pengumuman.index'));

        $this->assertDatabaseMissing('pengumuman', [
            'id_pengumuman' => $p->id_pengumuman,
        ]);
    }

    /**
     * 9. Hak Akses: Siswa Dilarang Membuat, Mengedit, dan Menghapus Pengumuman (403)
     */
    public function test_siswa_forbidden_from_creating_updating_and_deleting_pengumuman(): void
    {
        $p = Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Warta Penting',
            'isi' => 'Hanya admin yang boleh ubah.',
            'kategori' => 'Penting',
            'target' => 'Semua',
            'tanggal' => '2026-11-20',
        ]);

        $this->actingAs($this->siswa)->get(route('pengumuman.create'))->assertForbidden();
        $this->actingAs($this->siswa)->post(route('pengumuman.store'), [
            'judul' => 'Coba hack',
            'isi' => 'Coba hack',
            'kategori' => 'Penting',
            'target' => 'Semua',
            'tanggal' => '2026-11-20',
        ])->assertForbidden();

        $this->actingAs($this->siswa)->get(route('pengumuman.edit', $p->id_pengumuman))->assertForbidden();
        $this->actingAs($this->siswa)->put(route('pengumuman.update', $p->id_pengumuman), [
            'judul' => 'Coba hack update',
            'isi' => 'Coba hack update',
            'kategori' => 'Penting',
            'target' => 'Semua',
            'tanggal' => '2026-11-20',
        ])->assertForbidden();

        $this->actingAs($this->siswa)->delete(route('pengumuman.destroy', $p->id_pengumuman))->assertForbidden();
    }

    /**
     * 10. Hak Akses: Siswa Dilarang Melihat Detail Pengumuman Khusus Guru
     */
    public function test_siswa_forbidden_from_viewing_guru_target_pengumuman_detail(): void
    {
        $pGuru = Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Rapat Rahasia Guru',
            'isi' => 'Nilai rapor dan evaluasi dewan guru.',
            'kategori' => 'Penting',
            'target' => 'Guru',
            'tanggal' => '2026-11-25',
        ]);

        $this->actingAs($this->siswa)->get(route('pengumuman.show', $pGuru->id_pengumuman))->assertForbidden();

        // Tapi siswa boleh melihat detail pengumuman target Semua atau Siswa
        $pSiswa = Pengumuman::create([
            'id_users' => $this->admin->id_users,
            'judul' => 'Jadwal Libur Siswa',
            'isi' => 'Libur akhir pekan bersama.',
            'kategori' => 'Informasi',
            'target' => 'Siswa',
            'tanggal' => '2026-11-25',
        ]);

        $this->actingAs($this->siswa)->get(route('pengumuman.show', $pSiswa->id_pengumuman))->assertStatus(200);
    }

    /**
     * 11. Modul Laporan Terpadu: Admin & Guru Dapat Mengakses
     */
    public function test_admin_and_guru_can_access_laporan_index_with_filters(): void
    {
        $resAdmin = $this->actingAs($this->admin)->get(route('laporan.index'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertViewIs('laporan.index');
        $resAdmin->assertSee('Laporan &amp; Rekapitulasi Terpadu', false);

        $resGuru = $this->actingAs($this->guru)->get(route('laporan.index', ['tab' => 'absensi']));
        $resGuru->assertStatus(200);
        $resGuru->assertSee('Rekapitulasi Kehadiran per Siswa');
    }

    /**
     * 12. Modul Laporan Terpadu: Siswa Dilarang Mengakses (403 Forbidden)
     */
    public function test_siswa_forbidden_from_accessing_laporan(): void
    {
        $this->actingAs($this->siswa)->get(route('laporan.index'))->assertForbidden();
    }

    /**
     * 13. Filter Laporan Memengaruhi Agregasi Database
     */
    public function test_laporan_filters_affect_data_accurately(): void
    {
        // Absensi pada bulan November
        Absensi::create([
            'id_anggota' => $this->anggota->id_anggota,
            'tanggal' => '2026-11-10',
            'status' => 'Hadir',
        ]);

        // Kas pada bulan November
        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-10',
            'jenis' => 'Pemasukan',
            'nominal' => 75000,
            'keterangan' => 'Kas Nov',
        ]);

        // Kas pada bulan Desember (tidak boleh masuk filter bulan 11)
        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-12-10',
            'jenis' => 'Pemasukan',
            'nominal' => 25000,
            'keterangan' => 'Kas Des',
        ]);

        $response = $this->actingAs($this->admin)->get(route('laporan.index', [
            'bulan' => '11',
            'tahun' => '2026',
        ]));

        $response->assertStatus(200);
        $response->assertSee('75.000');
        $response->assertDontSee('100.000');
    }

    /**
     * 14. DataTables Endpoint Server-Side Mengembalikan JSON
     */
    public function test_anggota_datatables_ajax_endpoint_returns_json(): void
    {
        $response = $this->actingAs($this->admin)->get(route('anggota.data'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
    }

    /**
     * 15. Hak Akses DataTables: Siswa Dilarang Mengakses Endpoint DataTables (403)
     */
    public function test_siswa_forbidden_from_accessing_anggota_datatables_endpoint(): void
    {
        $this->actingAs($this->siswa)->get(route('anggota.data'))->assertForbidden();
    }

    /**
     * 16. Dashboard Chart.js: Memuat Variabel Data Statistik
     */
    public function test_dashboards_render_with_chart_data_for_admin_guru_and_siswa(): void
    {
        $resAdmin = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $resAdmin->assertStatus(200);
        $resAdmin->assertViewHas(['chartAbsensi', 'chartPiket', 'chartKas', 'chartJurusan']);

        $resGuru = $this->actingAs($this->guru)->get(route('guru.dashboard'));
        $resGuru->assertStatus(200);
        $resGuru->assertViewHas(['chartAbsensi', 'chartPiket']);

        $resSiswa = $this->actingAs($this->siswa)->get(route('user.dashboard'));
        $resSiswa->assertStatus(200);
        $resSiswa->assertViewHas(['chartAbsensi', 'chartPiket']);
    }

    /**
     * 17. Verifikasi Ketat: Tidak Ada DB::transaction atau DB::beginTransaction dalam Kode Controller
     */
    public function test_verification_that_no_db_transactions_or_triggers_are_used(): void
    {
        $controllerFiles = glob(app_path('Http/Controllers/*.php'));
        foreach ($controllerFiles as $file) {
            $content = file_get_contents($file);
            $this->assertStringNotContainsString('DB::transaction', $content, "Ditemukan DB::transaction pada file {$file}");
            $this->assertStringNotContainsString('DB::beginTransaction', $content, "Ditemukan DB::beginTransaction pada file {$file}");
            $this->assertStringNotContainsString('DB::commit', $content, "Ditemukan DB::commit pada file {$file}");
            $this->assertStringNotContainsString('DB::rollBack', $content, "Ditemukan DB::rollBack pada file {$file}");
        }
    }
}
