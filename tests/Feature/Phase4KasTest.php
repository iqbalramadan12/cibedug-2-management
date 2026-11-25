<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Kas;
use App\Models\Piket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class Phase4KasTest extends TestCase
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

        // 3. Akun Siswa & Data Anggota
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
     * 1. Database & Struktur Tabel Kas
     */
    public function test_kas_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('kas'));
        $this->assertTrue(Schema::hasColumns('kas', [
            'id_kas',
            'id_users',
            'tanggal',
            'jenis',
            'nominal',
            'keterangan',
            'created_at',
            'updated_at',
        ]));
    }

    /**
     * 2. Eloquent ORM & Relasi Antar Model
     */
    public function test_kas_belongs_to_user_and_user_has_many_kas(): void
    {
        $kas = Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-20',
            'jenis' => 'Pemasukan',
            'nominal' => 50000,
            'keterangan' => 'Kas mingguan siswa',
        ]);

        $this->assertInstanceOf(User::class, $kas->user);
        $this->assertEquals($this->admin->id_users, $kas->user->id_users);
        $this->assertTrue($this->admin->kas->contains($kas));
    }

    /**
     * 3. Akses Halaman Kas untuk Admin & Guru
     */
    public function test_admin_and_guru_can_view_kas_index(): void
    {
        $responseAdmin = $this->actingAs($this->admin)->get(route('kas.index'));
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertViewIs('kas.index');
        $responseAdmin->assertSee('Manajemen Keuangan Kas');

        $responseGuru = $this->actingAs($this->guru)->get(route('kas.index'));
        $responseGuru->assertStatus(200);
        $responseGuru->assertViewIs('kas.index');
    }

    /**
     * 4. Akses Halaman Kas untuk Siswa (Read-Only)
     */
    public function test_siswa_can_view_kas_read_only_dashboard(): void
    {
        $responseSiswa = $this->actingAs($this->siswa)->get(route('kas.index'));
        $responseSiswa->assertStatus(200);
        $responseSiswa->assertViewIs('kas.index_siswa');
        $responseSiswa->assertSee('Transparansi Keuangan Kas');
        $responseSiswa->assertDontSee('Catat Transaksi Kas');
    }

    /**
     * 5. CRUD: Tambah Transaksi Kas (Pemasukan & Pengeluaran)
     */
    public function test_admin_can_record_pemasukan_dan_pengeluaran(): void
    {
        $payloadPemasukan = [
            'tanggal' => '2026-11-25',
            'jenis' => 'Pemasukan',
            'nominal' => 100000,
            'keterangan' => 'Iuran kas kelas bulan November',
        ];

        $response1 = $this->actingAs($this->admin)->post(route('kas.store'), $payloadPemasukan);
        $response1->assertRedirect(route('kas.index'));
        $response1->assertSessionHas('success');

        $this->assertDatabaseHas('kas', [
            'id_users' => $this->admin->id_users,
            'jenis' => 'Pemasukan',
            'nominal' => 100000,
        ]);

        $payloadPengeluaran = [
            'tanggal' => '2026-11-26',
            'jenis' => 'Pengeluaran',
            'nominal' => 35000,
            'keterangan' => 'Beli spidol whiteboard dan penghapus',
        ];

        $response2 = $this->actingAs($this->guru)->post(route('kas.store'), $payloadPengeluaran);
        $response2->assertRedirect(route('kas.index'));

        $this->assertDatabaseHas('kas', [
            'id_users' => $this->guru->id_users,
            'jenis' => 'Pengeluaran',
            'nominal' => 35000,
        ]);
    }

    /**
     * 6. Validasi Form Transaksi Kas
     */
    public function test_form_validation_rejects_empty_and_invalid_data(): void
    {
        $invalidPayload = [
            'tanggal' => '',
            'jenis' => 'InvalidJenis',
            'nominal' => -10000,
            'keterangan' => '',
        ];

        $response = $this->actingAs($this->admin)->post(route('kas.store'), $invalidPayload);
        $response->assertSessionHasErrors(['tanggal', 'jenis', 'nominal', 'keterangan']);
        $this->assertDatabaseCount('kas', 0);
    }

    /**
     * 7. Update Transaksi Kas
     */
    public function test_admin_can_update_transaksi_kas(): void
    {
        $kas = Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-21',
            'jenis' => 'Pemasukan',
            'nominal' => 20000,
            'keterangan' => 'Kas lama',
        ]);

        $updatePayload = [
            'tanggal' => '2026-11-22',
            'jenis' => 'Pemasukan',
            'nominal' => 25000,
            'keterangan' => 'Kas dikoreksi',
        ];

        $response = $this->actingAs($this->admin)->put(route('kas.update', $kas->id_kas), $updatePayload);
        $response->assertRedirect(route('kas.index'));

        $this->assertDatabaseHas('kas', [
            'id_kas' => $kas->id_kas,
            'nominal' => 25000,
            'keterangan' => 'Kas dikoreksi',
        ]);
    }

    /**
     * 8. Hapus Transaksi Kas
     */
    public function test_admin_can_delete_transaksi_kas(): void
    {
        $kas = Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-21',
            'jenis' => 'Pengeluaran',
            'nominal' => 15000,
            'keterangan' => 'Salah catat',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('kas.destroy', $kas->id_kas));
        $response->assertRedirect(route('kas.index'));

        $this->assertDatabaseMissing('kas', [
            'id_kas' => $kas->id_kas,
        ]);
    }

    /**
     * 9. Perhitungan Ringkasan Keuangan (Total Pemasukan, Pengeluaran, Saldo Akhir)
     */
    public function test_financial_summary_calculations_pemasukan_pengeluaran_saldo(): void
    {
        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-20',
            'jenis' => 'Pemasukan',
            'nominal' => 100000,
            'keterangan' => 'Kas A',
        ]);

        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-21',
            'jenis' => 'Pemasukan',
            'nominal' => 50000,
            'keterangan' => 'Kas B',
        ]);

        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-22',
            'jenis' => 'Pengeluaran',
            'nominal' => 30000,
            'keterangan' => 'Beli sapu',
        ]);

        $totalPemasukan = Kas::where('jenis', 'Pemasukan')->sum('nominal');
        $totalPengeluaran = Kas::where('jenis', 'Pengeluaran')->sum('nominal');
        $saldoAkhir = $totalPemasukan - $totalPengeluaran;

        $this->assertEquals(150000, $totalPemasukan);
        $this->assertEquals(30000, $totalPengeluaran);
        $this->assertEquals(120000, $saldoAkhir);

        $response = $this->actingAs($this->admin)->get(route('kas.index'));
        $response->assertSee('150.000');
        $response->assertSee('30.000');
        $response->assertSee('120.000');
    }

    /**
     * 10. Pencarian dan Filter Jenis & Rentang Tanggal
     */
    public function test_search_and_filter_kas_by_jenis_and_tanggal(): void
    {
        $k1 = Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-01',
            'jenis' => 'Pemasukan',
            'nominal' => 50000,
            'keterangan' => 'Kas Mingguan Awal',
        ]);

        $k2 = Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-15',
            'jenis' => 'Pengeluaran',
            'nominal' => 20000,
            'keterangan' => 'Beli Alat Pel',
        ]);

        // Filter jenis Pemasukan
        $resJenis = $this->actingAs($this->admin)->get(route('kas.index', ['jenis' => 'Pemasukan']));
        $resJenis->assertSee('Kas Mingguan Awal');
        $resJenis->assertDontSee('Beli Alat Pel');

        // Filter rentang tanggal
        $resTanggal = $this->actingAs($this->admin)->get(route('kas.index', [
            'dari_tanggal' => '2026-11-10',
            'sampai_tanggal' => '2026-11-20',
        ]));
        $resTanggal->assertSee('Beli Alat Pel');
        $resTanggal->assertDontSee('Kas Mingguan Awal');

        // Search kata kunci
        $resSearch = $this->actingAs($this->admin)->get(route('kas.index', ['q' => 'Alat Pel']));
        $resSearch->assertSee('Beli Alat Pel');
        $resSearch->assertDontSee('Kas Mingguan Awal');
    }

    /**
     * 11. Sorting Kas
     */
    public function test_sorting_kas_by_nominal(): void
    {
        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-01',
            'jenis' => 'Pemasukan',
            'nominal' => 10000,
            'keterangan' => 'Kecil',
        ]);

        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-02',
            'jenis' => 'Pemasukan',
            'nominal' => 90000,
            'keterangan' => 'Besar',
        ]);

        $resSortDesc = $this->actingAs($this->admin)->get(route('kas.index', [
            'sort_by' => 'nominal',
            'sort_order' => 'desc',
        ]));
        $resSortDesc->assertStatus(200);
        $resSortDesc->assertSeeInOrder(['Besar', 'Kecil']);
    }

    /**
     * 12. Export Excel Kas
     */
    public function test_admin_and_guru_can_export_kas_excel(): void
    {
        Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-20',
            'jenis' => 'Pemasukan',
            'nominal' => 50000,
            'keterangan' => 'Test Export',
        ]);

        $response = $this->actingAs($this->admin)->get(route('kas.export.excel'));
        $response->assertStatus(200);
        $response->assertDownload('laporan-kas.xlsx');
    }

    /**
     * 13. Export PDF Kas
     */
    public function test_admin_and_guru_can_export_kas_pdf(): void
    {
        Kas::create([
            'id_users' => $this->guru->id_users,
            'tanggal' => '2026-11-20',
            'jenis' => 'Pengeluaran',
            'nominal' => 15000,
            'keterangan' => 'Beli kapur',
        ]);

        $response = $this->actingAs($this->guru)->get(route('kas.export.pdf'));
        $response->assertStatus(200);
        $response->assertDownload('laporan-kas.pdf');
    }

    /**
     * 14. Export Excel Absensi
     */
    public function test_admin_and_guru_can_export_absensi_excel(): void
    {
        Absensi::create([
            'id_anggota' => $this->anggota->id_anggota,
            'tanggal' => '2026-11-25',
            'status' => 'Hadir',
            'keterangan' => 'Tepat waktu',
        ]);

        $response = $this->actingAs($this->admin)->get(route('absensi.export.excel'));
        $response->assertStatus(200);
        $response->assertDownload('laporan-absensi.xlsx');
    }

    /**
     * 15. Export PDF Absensi
     */
    public function test_admin_and_guru_can_export_absensi_pdf(): void
    {
        Absensi::create([
            'id_anggota' => $this->anggota->id_anggota,
            'tanggal' => '2026-11-25',
            'status' => 'Izin',
            'keterangan' => 'Acara keluarga',
        ]);

        $response = $this->actingAs($this->guru)->get(route('absensi.export.pdf'));
        $response->assertStatus(200);
        $response->assertDownload('laporan-absensi.pdf');
    }

    /**
     * 16. Export Excel Piket
     */
    public function test_admin_and_guru_can_export_piket_excel(): void
    {
        Piket::create([
            'id_anggota' => $this->anggota->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Menyapu Ruang Kelas',
            'status' => 'Selesai',
        ]);

        $response = $this->actingAs($this->admin)->get(route('piket.export.excel'));
        $response->assertStatus(200);
        $response->assertDownload('laporan-piket.xlsx');
    }

    /**
     * 17. Export PDF Piket
     */
    public function test_admin_and_guru_can_export_piket_pdf(): void
    {
        Piket::create([
            'id_anggota' => $this->anggota->id_anggota,
            'tanggal' => '2026-11-25',
            'tugas' => 'Membersihkan Papan Tulis',
            'status' => 'Belum',
        ]);

        $response = $this->actingAs($this->guru)->get(route('piket.export.pdf'));
        $response->assertStatus(200);
        $response->assertDownload('laporan-piket.pdf');
    }

    /**
     * 18. Hak Akses: Siswa Dilarang Export dan Modifikasi Kas (403 Forbidden)
     */
    public function test_siswa_forbidden_from_exporting_and_modifying_kas(): void
    {
        $kas = Kas::create([
            'id_users' => $this->admin->id_users,
            'tanggal' => '2026-11-20',
            'jenis' => 'Pemasukan',
            'nominal' => 20000,
            'keterangan' => 'Kas tes hak akses',
        ]);

        // Siswa tidak boleh export kas Excel
        $this->actingAs($this->siswa)->get(route('kas.export.excel'))->assertForbidden();

        // Siswa tidak boleh export kas PDF
        $this->actingAs($this->siswa)->get(route('kas.export.pdf'))->assertForbidden();

        // Siswa tidak boleh export absensi Excel & PDF
        $this->actingAs($this->siswa)->get(route('absensi.export.excel'))->assertForbidden();
        $this->actingAs($this->siswa)->get(route('absensi.export.pdf'))->assertForbidden();

        // Siswa tidak boleh export piket Excel & PDF
        $this->actingAs($this->siswa)->get(route('piket.export.excel'))->assertForbidden();
        $this->actingAs($this->siswa)->get(route('piket.export.pdf'))->assertForbidden();

        // Siswa tidak boleh create / edit / delete kas
        $this->actingAs($this->siswa)->get(route('kas.create'))->assertForbidden();
        $this->actingAs($this->siswa)->post(route('kas.store'), [
            'tanggal' => '2026-11-20',
            'jenis' => 'Pemasukan',
            'nominal' => 10000,
            'keterangan' => 'Coba hack',
        ])->assertForbidden();
        $this->actingAs($this->siswa)->get(route('kas.edit', $kas->id_kas))->assertForbidden();
        $this->actingAs($this->siswa)->put(route('kas.update', $kas->id_kas), [
            'tanggal' => '2026-11-20',
            'jenis' => 'Pemasukan',
            'nominal' => 15000,
            'keterangan' => 'Coba ubah',
        ])->assertForbidden();
        $this->actingAs($this->siswa)->delete(route('kas.destroy', $kas->id_kas))->assertForbidden();
    }
}
