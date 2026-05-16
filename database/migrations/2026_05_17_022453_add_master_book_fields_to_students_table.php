<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Data Identitas Tambahan
            $table->string('nik', 20)->nullable()->after('nis');
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
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nik', 'no_kk', 'no_akta_lahir', 'anak_ke', 'jumlah_saudara',
                'tinggi_badan', 'berat_badan', 'golongan_darah',
                'ayah_nik', 'ayah_nama', 'ayah_pendidikan', 'ayah_pekerjaan', 'ayah_penghasilan',
                'ibu_nik', 'ibu_nama', 'ibu_pendidikan', 'ibu_pekerjaan', 'ibu_penghasilan',
                'wali_nama', 'wali_pekerjaan', 'wali_hubungan'
            ]);
        });
    }
};
