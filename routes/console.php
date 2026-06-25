<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune Chatbot History older than 7 days
Schedule::call(function () {
    $conversationsTable = config('ai.conversations.tables.conversations', 'agent_conversations');
    $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');

    $oldConversations = DB::table($conversationsTable)
        ->where('updated_at', '<', now()->subDays(7))
        ->pluck('id');

    if ($oldConversations->isNotEmpty()) {
        DB::table($messagesTable)
            ->whereIn('conversation_id', $oldConversations)
            ->delete();

        DB::table($conversationsTable)
            ->whereIn('id', $oldConversations)
            ->delete();
    }
})->daily();
