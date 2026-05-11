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
        Schema::create('day_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->json('level_ids')->nullable();
            $table->string('day');
            $table->foreignId('max_time_slot_id')->nullable()->constrained('time_slots')->onDelete('set null');
            $table->foreignId('mandatory_subject_id')->nullable()->constrained('subjects')->onDelete('set null');
            $table->foreignId('mandatory_time_slot_id')->nullable()->constrained('time_slots')->onDelete('set null');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_configs');
    }
};
