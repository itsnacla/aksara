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
            $table->string('wa_gateway_url')->nullable();
            $table->string('wa_api_key')->nullable();
            $table->string('wa_number')->nullable();
            $table->boolean('wa_notify_attendance')->default(false);
            $table->boolean('wa_notify_announcement')->default(false);
            
            $table->timestamps();
        });

        // Insert default data
        \App\Models\SchoolSetting::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Aksara Academic System',
                'motto' => 'Digital Education Excellence',
            ]
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
