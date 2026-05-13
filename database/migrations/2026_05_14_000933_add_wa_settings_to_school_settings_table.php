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
            if (!Schema::hasColumn('school_settings', 'is_wa_enabled')) {
                $table->boolean('is_wa_enabled')->default(false);
            }
            if (!Schema::hasColumn('school_settings', 'wa_gateway_provider')) {
                $table->string('wa_gateway_provider')->default('fonnte');
            }
            if (!Schema::hasColumn('school_settings', 'wa_gateway_url')) {
                $table->string('wa_gateway_url')->nullable();
            }
            if (!Schema::hasColumn('school_settings', 'wa_gateway_token')) {
                $table->string('wa_gateway_token')->nullable();
            }
            if (!Schema::hasColumn('school_settings', 'wa_gateway_phone_param')) {
                $table->string('wa_gateway_phone_param')->default('target');
            }
            if (!Schema::hasColumn('school_settings', 'wa_gateway_message_param')) {
                $table->string('wa_gateway_message_param')->default('message');
            }
            if (!Schema::hasColumn('school_settings', 'wa_notify_attendance')) {
                $table->boolean('wa_notify_attendance')->default(false);
            }
            if (!Schema::hasColumn('school_settings', 'wa_notify_announcement')) {
                $table->boolean('wa_notify_announcement')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropColumn([
                'is_wa_enabled',
                'wa_gateway_provider',
                'wa_gateway_url',
                'wa_gateway_token',
                'wa_gateway_phone_param',
                'wa_gateway_message_param',
                'wa_notify_attendance',
                'wa_notify_announcement',
            ]);
        });
    }
};
