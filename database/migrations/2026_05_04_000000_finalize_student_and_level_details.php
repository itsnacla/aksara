<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('nis')->nullable()->after('nisn');
            $table->string('previous_school')->nullable()->after('address');
            $table->boolean('lives_with_parent')->default(true)->after('phone');
            $table->string('village')->nullable()->after('address');
            $table->string('district')->nullable()->after('village');
            $table->string('city')->nullable()->after('district');
            $table->string('province')->nullable()->after('city');
        });

        Schema::table('levels', function (Blueprint $table) {
            $table->boolean('is_last_level')->default(false)->after('nama_tingkatan');
        });

        Schema::table('parents', function (Blueprint $table) {
            // Parent info
            $table->string('father_name')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('mother_occupation')->nullable();
            
            // Parent address breakdown
            $table->text('address')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            
            // Guardian info
            $table->string('guardian_name')->nullable();
            $table->string('guardian_occupation')->nullable();
            $table->text('guardian_address')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['nis', 'previous_school', 'lives_with_parent', 'village', 'district', 'city', 'province']);
        });

        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn('is_last_level');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn([
                'father_name', 'father_occupation', 
                'mother_name', 'mother_occupation',
                'address', 'village', 'district', 'city', 'province',
                'guardian_name', 'guardian_occupation', 'guardian_address'
            ]);
        });
    }
};
