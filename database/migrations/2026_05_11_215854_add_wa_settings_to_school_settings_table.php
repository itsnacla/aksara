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
        Schema::table('school_settings', function (Blueprint $table) {
            $table->string('wa_gateway_token')->nullable();
            $table->string('wa_gateway_provider')->default('fonnte');
            $table->boolean('is_wa_enabled')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['wa_gateway_token', 'wa_gateway_provider', 'is_wa_enabled']);
        });
    }
};
