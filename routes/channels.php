<?php

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat-messages.{chat_id}', function ($user, $chat_id) {
    $isMember = Chat::where('id', $chat_id)
        ->whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->exists();

    if ($isMember) {
        return [
            'id' => $user->id,
            'chat_id' => $chat_id,
        ];
    }

    return false;
});

Broadcast::channel('chats.{user_id}', function ($user, $user_id) {
    return $user->id !== $user_id;
});

Broadcast::channel('chat.{chat_id}', function (User $user, int $chat_id) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'chat_id' => $chat_id,
    ];
});
