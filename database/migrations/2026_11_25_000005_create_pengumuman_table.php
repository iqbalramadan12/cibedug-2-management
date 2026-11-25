<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id('id_pengumuman'); // Primary Key, Auto Increment
            $table->unsignedInteger('id_users'); // Foreign Key ke users.id_users
            $table->string('judul');
            $table->text('isi');
            $table->string('kategori', 50); // Informasi, Kegiatan, Akademik, Penting
            $table->string('target', 50); // Semua, Guru, Siswa
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('id_users')->references('id_users')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};
