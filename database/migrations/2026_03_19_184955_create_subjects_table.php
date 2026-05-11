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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel', 20)->nullable()->unique();
            $table->string('nama_mapel', 100);
            $table->boolean('is_umum')->default(false);
            $table->integer('total_jp')->default(2);
            $table->boolean('is_one_day_finish')->default(false);
            $table->integer('scheduling_priority')->default(1);
            $table->foreignId('level_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('kkm')->default(75);
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
