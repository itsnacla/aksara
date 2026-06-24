<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Ai\Migrations\AiMigration;

return new class extends AiMigration
{
    public function up(): void
    {
        // Table for dynamic AI SDK configuration
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('primary_provider')->default('google');
            $table->string('fallback_providers')->nullable();
            $table->jsonb('settings')->nullable(); // Stores all provider-specific configs
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('agent_conversations', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->foreignId('user_id')->nullable();
            $table->string('title');
            $table->timestamps();
            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('agent_conversation_messages', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('conversation_id', 36)->index();
            $table->foreignId('user_id')->nullable();
            $table->string('agent');
            $table->string('role', 25);
            $table->text('content');
            $table->text('attachments')->nullable();
            $table->text('tool_calls')->nullable();
            $table->text('tool_results')->nullable();
            $table->text('usage')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'user_id', 'updated_at'], 'conversation_index');
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
        Schema::dropIfExists('agent_conversations');
        Schema::dropIfExists('agent_conversation_messages');
    }
};
