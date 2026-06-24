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
        Schema::table('academic_years', function (Blueprint $table) {
            $table->date('rapor_date')->nullable()->after('is_active');
            $table->date('schedule_date')->nullable()->after('rapor_date');
            $table->date('attendance_date')->nullable()->after('schedule_date');
            $table->date('pelengkap_rapor_date')->nullable()->after('attendance_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn([
                'rapor_date',
                'schedule_date',
                'attendance_date',
                'pelengkap_rapor_date',
            ]);
        });
    }
};
