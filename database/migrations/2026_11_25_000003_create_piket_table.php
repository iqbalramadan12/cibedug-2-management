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
        Schema::create('piket', function (Blueprint $table) {
            $table->increments('id_piket');
            $table->unsignedInteger('id_anggota');
            $table->date('tanggal');
            $table->string('tugas', 100);
            $table->string('status', 20)->default('Belum'); // Selesai / Belum
            $table->timestamps();

            $table->foreign('id_anggota')->references('id_anggota')->on('anggota')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('piket');
    }
};
