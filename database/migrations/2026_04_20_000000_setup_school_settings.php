<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('npsn')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_pemda')->nullable();
            $table->string('address')->nullable();
            $table->string('village')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('motto')->nullable();
            
            // WA Settings
            $table->boolean('is_wa_enabled')->default(false);
            $table->string('wa_gateway_provider')->default('fonnte');
            $table->string('wa_gateway_url')->nullable();
            $table->string('wa_gateway_token')->nullable();
            $table->string('wa_gateway_phone_param')->default('target');
            $table->string('wa_gateway_message_param')->default('message');
            $table->boolean('wa_notify_attendance')->default(false);
            $table->boolean('wa_notify_announcement')->default(false);
            
            $table->timestamps();
        });

        // Insert default data
        \App\Models\SchoolSetting::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Aksara Academic System',
                'motto' => 'Membangun Karakter, Meraih Prestasi',
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
