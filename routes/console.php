<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune Chatbot History older than 7 days
\Illuminate\Support\Facades\Schedule::call(function () {
    $conversationsTable = config('ai.conversations.tables.conversations', 'agent_conversations');
    $messagesTable = config('ai.conversations.tables.messages', 'agent_conversation_messages');

    $oldConversations = \Illuminate\Support\Facades\DB::table($conversationsTable)
        ->where('updated_at', '<', now()->subDays(7))
        ->pluck('id');

    if ($oldConversations->isNotEmpty()) {
        \Illuminate\Support\Facades\DB::table($messagesTable)
            ->whereIn('conversation_id', $oldConversations)
            ->delete();

        \Illuminate\Support\Facades\DB::table($conversationsTable)
            ->whereIn('id', $oldConversations)
            ->delete();
    }
})->daily();
