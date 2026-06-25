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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('gelar_depan', 20)->nullable();
            $table->string('nip', 20)->nullable()->unique();
            $table->string('gelar_belakang', 50)->nullable();
            $table->string('kode_guru', 10)->nullable()->unique();
            $table->boolean('is_walikelas')->default(false);
            $table->boolean('is_kepalasekolah')->default(false);
            $table->string('no_whatsapp', 20)->nullable();
            $table->string('status')->default('aktif'); // aktif, mutasi, pensiun, berhenti
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
