<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $auth = auth()->user();

        $chats = Chat::query()
            ->whereRelation('members', 'user_id', $auth->id)
            ->with([
                'members' => function ($query) use ($auth) {
                    $query->where('user_id', '!=', $auth->id);
                    $query->with('user:id,name,avatar');
                },
                'lastMessage' => function ($query) use ($auth) {
                    $query->select([
                        'chat_messages.*',
                        DB::raw("CASE WHEN user_id = {$auth->id} THEN TRUE ELSE FALSE END as is_mine"),
                    ]);
                },
                'lastMessage.user:id,name',
            ])
            ->latest()
            ->paginate(20)
            ->toResourceCollection();

        return Inertia::render('Chat/Index', [
            'chats' => $chats,
        ]);
    }

    public function getChatMessages(Chat $chat)
    {
        $auth = auth()->user();

        $messages = ChatMessage::query()
            ->select([
                'chat_messages.*',
                DB::raw("CASE WHEN user_id = {$auth->id} THEN TRUE ELSE FALSE END as is_mine"),
            ])
            ->with([
                'user:id,name,avatar',
            ])
            ->withCount(['statuses as is_readed' => function ($query) use ($auth) {
                $query->where('user_id', '!=', $auth->id)
                    ->whereNotNull('read_at');
            }])
            ->where('chat_id', $chat->id)
            ->oldest('id')
            ->paginate(20)
            ->toResourceCollection();

        return Inertia::render('Chat/Index', [
            'active_chat_id' => $chat->id,
            'messages' => $messages,
        ]);
        
    }
}
