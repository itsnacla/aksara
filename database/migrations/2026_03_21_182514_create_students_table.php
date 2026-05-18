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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->constrained('parents')->cascadeOnDelete();
            $table->boolean('is_buku_induk_generated')->default(false);
            $table->foreignId('study_group_id')->nullable()->constrained('study_groups')->onDelete('set null');
            $table->string('nisn', 10)->unique();
            $table->string('nis')->nullable();
            
            // Data Identitas Tambahan
            $table->string('nik', 20)->nullable();
            $table->string('no_kk', 20)->nullable();
            $table->string('no_akta_lahir', 50)->nullable();
            $table->integer('anak_ke')->nullable();
            $table->integer('jumlah_saudara')->nullable();
            
            // Data Kesehatan
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->string('golongan_darah', 5)->nullable();
            
            // Data Orang Tua Detail
            $table->string('ayah_nik', 20)->nullable();
            $table->string('ayah_nama')->nullable();
            $table->string('ayah_pendidikan')->nullable();
            $table->string('ayah_pekerjaan')->nullable();
            $table->string('ayah_penghasilan')->nullable();
            
            $table->string('ibu_nik', 20)->nullable();
            $table->string('ibu_nama')->nullable();
            $table->string('ibu_pendidikan')->nullable();
            $table->string('ibu_pekerjaan')->nullable();
            $table->string('ibu_penghasilan')->nullable();
            
            $table->string('wali_nama')->nullable();
            $table->string('wali_pekerjaan')->nullable();
            $table->string('wali_hubungan')->nullable();

            $table->string('status')->default('aktif'); // aktif, lulus, mutasi, keluar
            $table->string('pob')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('religion')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('lives_with_parent')->default(true);
            $table->text('address')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('previous_school')->nullable();
            $table->timestamps();
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
