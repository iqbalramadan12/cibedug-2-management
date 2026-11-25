<?php

namespace Database\Seeders;

use App\Models\Anggota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Admin
        User::create([
            'nama' => 'Budi Santoso (OPS)',
            'email' => 'admin@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. Akun Guru / Pembimbing Siswa
        User::create([
            'nama' => 'Sri Wahyuni, S.Pd.',
            'email' => 'guru@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'guru',
        ]);

        // 3. Akun Siswa (User)
        $siswaUser = User::create([
            'nama' => 'Ahmad Fauzi',
            'email' => 'siswa@smkcibedug2.sch.id',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Data Anggota untuk Siswa
        Anggota::create([
            'id_users' => $siswaUser->id_users,
            'nis' => '1024501',
            'kelas' => 'XI-RPL 2',
            'jurusan' => 'Rekayasa Perangkat Lunak',
            'no_hp' => '0812-3456-7890',
        ]);
    }
}
