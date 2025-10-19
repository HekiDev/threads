<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat-messages.{chat_id}', function ($user, $chat_id) {
    return true;
});

Broadcast::channel('chat.{chat_id}', function (User $user, int $chat_id) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'chat_id' => $chat_id,
    ];
});
