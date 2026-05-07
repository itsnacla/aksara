<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('gemini'); // gemini, openai, groq
            $table->string('fallback_providers')->nullable(); // comma-separated: "groq,openai"
            $table->string('gemini_api_key')->nullable();
            $table->string('gemini_model')->default('gemini-2.0-flash');
            $table->string('openai_api_key')->nullable();
            $table->string('openai_model')->default('gpt-4o-mini');
            $table->string('openai_base_url')->default('https://api.openai.com/v1');
            $table->string('groq_api_key')->nullable();
            $table->string('groq_model')->default('llama-3.3-70b-versatile');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
    }
};
