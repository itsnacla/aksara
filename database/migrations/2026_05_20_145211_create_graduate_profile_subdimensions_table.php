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
        Schema::create('graduate_profile_subdimensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('graduate_profile_id')->constrained('graduate_profiles')->cascadeOnDelete();
            $table->text('subdimensi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduate_profile_subdimensions');
    }
};
