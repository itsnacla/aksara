<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversations.{conversationId}', function ($user, $conversationId) {
    // For now, allow all authenticated users to listen to their conversations
    // You can add more strict checks here if needed (e.g., check if user owns the conversation)
    return true;
});
