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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('nama_jam'); // Jam Pertama, Jam Kedua, dst
            $table->time('waktu_mulai');
            $table->time('waktu_selesai');
            $table->boolean('is_istirahat')->default(false);
            $table->integer('urutan'); // Untuk sorting
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
