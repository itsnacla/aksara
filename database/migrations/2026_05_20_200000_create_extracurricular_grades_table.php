<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->enum('predikat', ['A', 'B', 'C', 'D']);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_grades');
    }
};
