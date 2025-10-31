<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessageStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                'chat_message_id' => $message->id,
                'received_at' => $isMine ? $timestamp : null,
                'read_at' => $isMine ? $timestamp : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('chat_members')->insert($membersArr);
        DB::table('chat_message_statuses')->insert($statusArr);

        return [$chat, $message];
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
                'chat_message_id' => $message->id,
                'received_at' => $isMine ? $timestamp : null,
                'read_at' => $isMine ? $timestamp : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('chat_message_statuses')->insert($data);

        return $chat;
    }

    public function storeMessageStatus($auth, $chat, $message, $recipients): void
    {
        $statusArr = [];
        $timestamp = now();

        foreach ($chat->members as $member) {
            $isMine = $auth->id === $member->user_id;
            $isOnline = false;

            if (! $isMine) {
                // Determine if this recipient is online
                $isOnline = in_array($member->user_id, array_column($recipients, 'id'));
            }

            $statusArr[] = [
                'user_id' => $member->user_id,
                'chat_message_id' => $message->id,
                'received_at' => $isMine || $isOnline ? $timestamp : null,
                'read_at' => $isMine || $isOnline ? $timestamp : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }
        DB::table('chat_message_statuses')->insert($statusArr);
    }

    public function updateUnreadMessages($auth, $chat)
    {
        $query = ChatMessageStatus::whereRelation('chatMessage', 'chat_id', $chat->id)
            ->whereNull('received_at')
            ->whereNull('read_at')
            ->where('user_id', $auth->id);

        if (! $query->exists()) {
            return;
        }

        $query->update([
            'received_at' => now(),
            'read_at' => now(),
        ]);
    }

    public function getSubsribedUsers($chat_id)
    {
        $config = config('broadcasting.connections.reverb');
        $host = "{$config['options']['scheme']}://{$config['options']['host']}:{$config['options']['port']}";
        $appId = $config['app_id'];
        $key = $config['key'];
        $secret = $config['secret'];

        $channelName = 'presence-chat-messages.'.$chat_id;
        $path = "/apps/{$appId}/channels/{$channelName}/users";
        $method = 'GET';
        $queryParams = [
            'auth_key' => $key,
            'auth_timestamp' => time(),
            'auth_version' => '1.0',
        ];

        // Build the string to sign
        $stringToSign = "{$method}\n{$path}\n" . http_build_query($queryParams);

        // Create the HMAC SHA256 signature
        $authSignature = hash_hmac('sha256', $stringToSign, $secret);

        $queryParams['auth_signature'] = $authSignature;

        // Build final URL
        $url = "{$host}{$path}?" . http_build_query($queryParams);

        $users = [];
        try {
            // Send request
            $response = Http::get($url);

            if ($response->successful()) {
                $users = $response->json()['users'] ?? [];
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return $users;
    }
}