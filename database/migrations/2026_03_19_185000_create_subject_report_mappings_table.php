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
        Schema::create('subject_report_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('kurikulum', 100);
            $table->foreignId('level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('nama_lokal', 100);
            $table->integer('no_urut');
            $table->timestamps();

            // Prevent duplicate mapping of same subject in same curriculum, level
            $table->unique(['kurikulum', 'level_id', 'subject_id'], 'unique_curriculum_level_subject');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_report_mappings');
    }
};
