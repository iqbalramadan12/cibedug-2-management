# DOKUMENTASI TEKNIS: PEMETAAN ELOQUENT ORM KE RAW SQL QUERY
## Project: Cibedug 2 Management (SMK Cibedug — Kelas XI RPL 2)
### Standar Rubrik SAS PPLG 2026/2027

Dokumen ini memetakan implementasi query **Eloquent ORM** yang digunakan pada modul aplikasi ke dalam bentuk **Raw SQL Query (MySQL 8.0)** yang ekuivalen untuk tujuan pembelajaran, analisa performa, dan pemenuhan rubrik penilaian SAS PPLG.

---

### KASUS 1: Mengambil Seluruh Data Anggota Beserta Data Pengguna (Eager Loading)
* **Tujuan Query:** Menampilkan daftar siswa pada menu *Manajemen Anggota* dengan menyertakan nama dan email user tanpa terkena masalah N+1 Query.
* **Tabel yang Terlibat:** `anggota`, `users`.

#### A. Kode Eloquent ORM
```php
$anggotaList = Anggota::with('user')
    ->orderBy('id_anggota', 'desc')
    ->get();
```

#### B. Raw SQL Query (Ekuivalen)
Secara internal, mekanisme *Eager Loading* Laravel menjalankan 2 query terindeks:
```sql
-- Query 1: Mengambil entitas anggota
SELECT * FROM `anggota` ORDER BY `id_anggota` DESC;

-- Query 2: Mengambil entitas user yang berelasi via klausa IN
SELECT * FROM `users` WHERE `users`.`id_users` IN (1, 2, 3, 4, 5);
```
Jika dikonversikan menjadi satu query SQL utuh menggunakan JOIN:
```sql
SELECT 
    `anggota`.`id_anggota`,
    `anggota`.`id_users`,
    `anggota`.`nis`,
    `anggota`.`kelas`,
    `anggota`.`jurusan`,
    `anggota`.`no_hp`,
    `users`.`nama`,
    `users`.`email`,
    `users`.`role`
FROM `anggota`
INNER JOIN `users` ON `anggota`.`id_users` = `users`.`id_users`
ORDER BY `anggota`.`id_anggota` DESC;
```

#### C. Analisa Struktur Query
* **Method ORM:** `with('user')` (Eager loading relasi `belongsTo`), `orderBy()`, `get()`.
* **Kondisi JOIN:** `anggota.id_users = users.id_users` (Foreign Key ke Primary Key).
* **ORDER BY:** `anggota.id_anggota DESC` (Data anggota terbaru di atas).

---

### KASUS 2: Mengambil Rekapitulasi Presensi Berdasarkan Tanggal Spesifik
* **Tujuan Query:** Menampilkan daftar kehadiran siswa pada tanggal tertentu di jurnal absensi.
* **Tabel yang Terlibat:** `absensi`, `anggota`, `users`.

#### A. Kode Eloquent ORM
```php
$absensiList = Absensi::with(['anggota.user'])
    ->whereDate('tanggal', '2026-11-25')
    ->orderBy('id_absensi', 'desc')
    ->get();
```

#### B. Raw SQL Query (Ekuivalen)
```sql
SELECT 
    `absensi`.`id_absensi`,
    `absensi`.`id_anggota`,
    `absensi`.`tanggal`,
    `absensi`.`status`,
    `absensi`.`keterangan`,
    `anggota`.`nis`,
    `anggota`.`kelas`,
    `users`.`nama` AS `nama_siswa`
FROM `absensi`
INNER JOIN `anggota` ON `absensi`.`id_anggota` = `anggota`.`id_anggota`
INNER JOIN `users` ON `anggota`.`id_users` = `users`.`id_users`
WHERE DATE(`absensi`.`tanggal`) = '2026-11-25'
ORDER BY `absensi`.`id_absensi` DESC;
```

#### C. Analisa Struktur Query
* **Method ORM:** `whereDate('tanggal', ...)` memastikan kompatibilitas format tanggal (YYYY-MM-DD).
* **Kondisi WHERE:** `DATE(absensi.tanggal) = '2026-11-25'`.
* **Kondisi JOIN:** Dua level JOIN (`absensi` ke `anggota`, lalu `anggota` ke `users`).
* **ORDER BY:** `absensi.id_absensi DESC`.

---

### KASUS 3: Pencarian Anggota Berdasarkan Nama Menggunakan Relasi (`whereHas`)
* **Tujuan Query:** Menyaring siswa di mana kata kunci pencarian cocok dengan kolom `nama` pada tabel `users`.
* **Tabel yang Terlibat:** `anggota`, `users`.

#### A. Kode Eloquent ORM
```php
$anggota = Anggota::with('user')
    ->whereHas('user', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%");
    })
    ->get();
```

#### B. Raw SQL Query (Ekuivalen)
```sql
SELECT `anggota`.* 
FROM `anggota`
WHERE EXISTS (
    SELECT 1 
    FROM `users` 
    WHERE `users`.`id_users` = `anggota`.`id_users` 
      AND `users`.`nama` LIKE '%Ahmad%'
);
```

#### C. Analisa Struktur Query
* **Method ORM:** `whereHas()` menghasilkan subquery dengan predikat `WHERE EXISTS (...)`.
* **Subquery WHERE:** Mencocokkan relasi foreign key `users.id_users = anggota.id_users` dan filter substring `LIKE '%keyword%'`.

---

### KASUS 4: Mengambil Jadwal Piket Berdasarkan Status dan Eager Loading
* **Tujuan Query:** Menampilkan penugasan piket yang telah selesai atau belum dikerjakan.
* **Tabel yang Terlibat:** `piket`, `anggota`, `users`.

#### A. Kode Eloquent ORM
```php
$piketList = Piket::with(['anggota.user'])
    ->where('status', 'Selesai')
    ->orderBy('tanggal', 'desc')
    ->get();
```

#### B. Raw SQL Query (Ekuivalen)
```sql
SELECT 
    `piket`.`id_piket`,
    `piket`.`id_anggota`,
    `piket`.`tanggal`,
    `piket`.`tugas`,
    `piket`.`status`,
    `anggota`.`nis`,
    `users`.`nama` AS `nama_siswa`
FROM `piket`
INNER JOIN `anggota` ON `piket`.`id_anggota` = `anggota`.`id_anggota`
INNER JOIN `users` ON `anggota`.`id_users` = `users`.`id_users`
WHERE `piket`.`status` = 'Selesai'
ORDER BY `piket`.`tanggal` DESC;
```

#### C. Analisa Struktur Query
* **Method ORM:** `where('status', 'Selesai')`, `orderBy('tanggal', 'desc')`.
* **Kondisi WHERE:** `piket.status = 'Selesai'`.
* **JOIN:** Relasi `piket.id_anggota -> anggota.id_anggota` dan `anggota.id_users -> users.id_users`.

---

### KASUS 5: Menghitung Agregasi Keuangan Kas (Pemasukan, Pengeluaran, Saldo)
* **Tujuan Query:** Menghitung total pembukuan keuangan kelas secara akurat langsung dari mesin database.
* **Tabel yang Terlibat:** `kas`.

#### A. Kode Eloquent ORM
```php
$totalPemasukan = Kas::where('jenis', 'Pemasukan')->sum('nominal');
$totalPengeluaran = Kas::where('jenis', 'Pengeluaran')->sum('nominal');
$saldoAkhir = $totalPemasukan - $totalPengeluaran;
```

#### B. Raw SQL Query (Ekuivalen)
Query terpisah:
```sql
-- Pemasukan
SELECT COALESCE(SUM(`nominal`), 0) AS `total_pemasukan` FROM `kas` WHERE `jenis` = 'Pemasukan';

-- Pengeluaran
SELECT COALESCE(SUM(`nominal`), 0) AS `total_pengeluaran` FROM `kas` WHERE `jenis` = 'Pengeluaran';
```
Atau dalam Single Aggregation Query:
```sql
SELECT 
    COALESCE(SUM(CASE WHEN `jenis` = 'Pemasukan' THEN `nominal` ELSE 0 END), 0) AS `total_pemasukan`,
    COALESCE(SUM(CASE WHEN `jenis` = 'Pengeluaran' THEN `nominal` ELSE 0 END), 0) AS `total_pengeluaran`,
    (COALESCE(SUM(CASE WHEN `jenis` = 'Pemasukan' THEN `nominal` ELSE 0 END), 0) - 
     COALESCE(SUM(CASE WHEN `jenis` = 'Pengeluaran' THEN `nominal` ELSE 0 END), 0)) AS `saldo_akhir`
FROM `kas`;
```

#### C. Analisa Struktur Query
* **Method ORM:** `where()`, `sum('nominal')` (Fungsi agregat SQL SUM).
* **Agregasi:** Menggunakan `SUM` dengan fungsi `COALESCE` untuk mengantisipasi nilai `NULL` saat tabel kosong.

---

### KASUS 6: Mengambil Pengumuman Sesuai Hak Akses Target Penerima
* **Tujuan Query:** Memastikan siswa hanya dapat melihat pengumuman yang ditujukan untuk sasaran 'Semua' atau 'Siswa'.
* **Tabel yang Terlibat:** `pengumuman`, `users`.

#### A. Kode Eloquent ORM
```php
$pengumumanList = Pengumuman::with('user')
    ->whereIn('target', ['Semua', 'Siswa'])
    ->orderBy('tanggal', 'desc')
    ->orderBy('id_pengumuman', 'desc')
    ->get();
```

#### B. Raw SQL Query (Ekuivalen)
```sql
SELECT 
    `pengumuman`.`id_pengumuman`,
    `pengumuman`.`id_users`,
    `pengumuman`.`judul`,
    `pengumuman`.`isi`,
    `pengumuman`.`kategori`,
    `pengumuman`.`target`,
    `pengumuman`.`tanggal`,
    `users`.`nama` AS `nama_pembuat`,
    `users`.`role` AS `role_pembuat`
FROM `pengumuman`
INNER JOIN `users` ON `pengumuman`.`id_users` = `users`.`id_users`
WHERE `pengumuman`.`target` IN ('Semua', 'Siswa')
ORDER BY `pengumuman`.`tanggal` DESC, `pengumuman`.`id_pengumuman` DESC;
```

#### C. Analisa Struktur Query
* **Method ORM:** `whereIn('target', [...])` membatasi data berdasarkan himpunan nilai.
* **Kondisi WHERE:** `pengumuman.target IN ('Semua', 'Siswa')`.
* **JOIN:** `pengumuman.id_users = users.id_users` untuk memuat informasi akun pembuat warta.
* **ORDER BY:** Multiple order criteria `tanggal DESC, id_pengumuman DESC`.
