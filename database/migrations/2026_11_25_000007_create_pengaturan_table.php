<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id('id_pengaturan');
            $table->string('kunci', 100)->unique();
            $table->text('nilai')->nullable();
            $table->timestamps();
        });

        // Data awal pengaturan Rayon Cibedug 2
        DB::table('pengaturan')->insert([
            ['kunci' => 'nama_rayon', 'nilai' => 'Rayon Cibedug 2', 'created_at' => now(), 'updated_at' => now()],
            ['kunci' => 'institusi', 'nilai' => 'SMK Wikrama Bogor', 'created_at' => now(), 'updated_at' => now()],
            ['kunci' => 'slogan_rayon', 'nilai' => 'Tempat tumbuh, belajar, dan membangun kebersamaan.', 'created_at' => now(), 'updated_at' => now()],
            ['kunci' => 'foto_hero', 'nilai' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
