<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('invocation_id')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message');
            $table->text('response')->nullable();
            $table->string('status'); // 'success', 'failed'
            $table->text('error_message')->nullable();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->float('latency_seconds')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_request_logs');
    }
};
