<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('p5_group_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_group_id')->constrained('p5_groups')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['p5_group_id', 'student_id']); // Prevent duplicate memberships
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p5_group_student');
    }
};
