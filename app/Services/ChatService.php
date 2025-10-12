<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Chat;

class ChatService
{
    public function new($auth, $members, $request)
    {
        $chat = Chat::create(['user_id' => $auth->id]);
        $message = $chat->messages()->create([
            'user_id' => $auth->id,
            'message' => $request->message,
        ]);

        $membersArr = [];
        $statusArr = [];
        $timestamp = now();

        foreach ($members as $member) {
            $isMine = $auth->id === $member;

            $membersArr[] = [
                'user_id' => $member,
                'chat_id' => $chat->id,
                'is_creator' => $isMine ? true : false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $statusArr[] = [
                'user_id' => $member,
                'chat_id' => $chat->id,
                'chat_message_id' => $message->id,
                'received_at' => $isMine ? $timestamp : null,
                'read_at' => $isMine ? $timestamp : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        $chat->members()->insert($membersArr);
        $message->statuses()->insert($statusArr);

        return $chat;
    }

    public function existing($auth, $existingChat, $members, $request)
    {
        $chat = Chat::where('id', $existingChat->chat_id)->first();

        $message = $chat->messages()->create([
            'user_id' => $auth->id,
            'message' => $request->message,
        ]);

        $data = [];
        $timestamp = now();
        foreach ($members as $member) {
            $isMine = $auth->id === $member;

            $data[] = [
                'user_id' => $member,
                'chat_id' => $chat->id,
                'chat_message_id' => $message->id,
                'received_at' => $isMine ? $timestamp : null,
                'read_at' => $isMine ? $timestamp : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        $message->statuses()->insert($data);

        return $chat;
    }
}