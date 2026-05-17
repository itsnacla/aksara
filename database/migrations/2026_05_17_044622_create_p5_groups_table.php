<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p5_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete(); // Koordinator
            $table->string('name'); // Nama Kelompok, misal: 9.1
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p5_groups');
    }
};
