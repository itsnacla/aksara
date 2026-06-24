<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p5_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_theme_id')->constrained('p5_themes')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('fase', 10)->nullable();
            $table->string('name'); // Judul Kegiatan
            $table->text('target_description')->nullable(); // Tujuan Akhir
            $table->json('graduate_profile')->nullable(); // Profil Lulusan (Dimensi/Elemen)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p5_projects');
    }
};
