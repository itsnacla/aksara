<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->dropColumn('nama_staff');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('nama_guru');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn('nama_wali');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('nama_siswa');
        });
    }
    
    public function down(): void
    {
        Schema::table('staffs', function (Blueprint $table) {
            $table->string('nama_staff', 100)->nullable()->after('user_id');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->string('nama_guru', 100)->after('nip');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->string('nama_wali', 100)->after('user_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('nama_siswa', 100)->after('nisn');
        });
    }
};
