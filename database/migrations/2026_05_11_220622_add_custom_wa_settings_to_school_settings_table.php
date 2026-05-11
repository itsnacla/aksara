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
            $table->string('wa_gateway_url')->nullable();
            $table->string('wa_gateway_phone_param')->default('target');
            $table->string('wa_gateway_message_param')->default('message');
        });
    }

    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn(['wa_gateway_url', 'wa_gateway_phone_param', 'wa_gateway_message_param']);
        });
    }
};
