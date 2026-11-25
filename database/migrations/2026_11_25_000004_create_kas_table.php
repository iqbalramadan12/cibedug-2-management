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
        Schema::create('kas', function (Blueprint $table) {
            $table->id('id_kas'); // BIGINT UNSIGNED, AUTO_INCREMENT, PRIMARY KEY
            $table->unsignedInteger('id_users'); // Foreign Key ke users.id_users
            $table->date('tanggal');
            $table->string('jenis', 20); // Pemasukan / Pengeluaran
            $table->decimal('nominal', 15, 2);
            $table->text('keterangan');
            $table->timestamps();

            // Aturan penghapusan restrict agar riwayat keuangan terlindungi
            $table->foreign('id_users')->references('id_users')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kas');
    }
};
