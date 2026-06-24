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
        Schema::create('cocurriculars', function (Blueprint $table) {
            $table->id();
            $table->string('tema'); // Contoh: Gaya Hidup Berkelanjutan
            $table->string('nama_projek'); // Contoh: Sekolahku Bebas Sampah
            $table->string('fase', 10); // Contoh: Fase D (untuk SMP)
            $table->text('deskripsi')->nullable();
            $table->string('tahun_ajaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cocurriculars');
    }
};
