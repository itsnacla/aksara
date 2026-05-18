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
        Schema::create('student_rapors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            
            // Attendance metrics saved at generation time
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpha')->default(0);

            // AI/Teacher written comments
            $table->text('catatan_wali_kelas')->nullable();

            // Promotion settings (Even semesters)
            $table->boolean('is_naik')->nullable(); // true = Naik, false = Tidak Naik, null = N/A
            $table->string('kenaikan_kelas_to')->nullable(); // Target grade promotion name
            $table->boolean('is_published')->default(false);

            $table->timestamps();

            // Symmetrical report mapping configuration
            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_rapors');
    }
};
