<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatResource;
use App\Http\Resources\SearchChatUserResource;
use App\Models\Chat;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService
    ) {}

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
            ->orderByDesc(function ($query) {
                $query->select('id')
                    ->from('chat_messages')
                    ->whereColumn('chat_id', 'chats.id')
                    ->latest('created_at')
                    ->limit(1);
            })
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

    public function storeChat(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:500',
        ]);

        $auth = auth()->user();

        $members = [$auth->id, $request->integer('user_id')];

        $existingChat = ChatMember::whereIn('user_id', $members)
            ->select('chat_id')
            ->groupBy('chat_id')
            ->havingRaw('COUNT(DISTINCT user_id) = 2')
            ->first();

        if ($existingChat) {
            $chat = $this->chatService->existing($auth, $existingChat, $members, $request);
            $chat->load([
                'members' => function ($query) use ($auth) {
                    $query->where('user_id', '!=', $auth->id);
                    $query->with('user:id,name,avatar');
                },
            ]);

            return response()->json([
                'chat' => new ChatResource($chat),
                'message' => 'You already have a chat with this user.'
            ], 201);
        }

        $chat = $this->chatService->new($auth, $members, $request);
        $chat->load([
            'members' => function ($query) use ($auth) {
                $query->where('user_id', '!=', $auth->id);
                $query->with('user:id,name,avatar');
            },
        ]);

        return response()->json([
            'chat' => new ChatResource($chat),
            'message' => 'Chat created successfully.',
        ], 201);
    }

    public function searchChatMembers(Request $request)
    {
        $auth = auth()->user();

        $data = User::query()
            ->select('id', 'name', 'avatar')
            ->where('id', '!=', $auth->id)
            ->whereNotIn('id', function ($query) use ($auth) {
                $query->select('user_id')
                    ->from('chat_members')
                    ->whereIn('chat_id', function ($sub) use ($auth) {
                        $sub->select('chat_id')
                            ->from('chat_members')
                            ->where('user_id', $auth->id);
                    })
                    ->where('user_id', '!=', $auth->id);
            })
            ->when($request->search, function ($query) use ($request) {
                $query->whereAny([
                    'name',
                ], 'like', '%' . $request->search . '%');
            })
            ->limit(10)
            ->orderBy('name')
            ->get()
            ->toResourceCollection(SearchChatUserResource::class);

        return response()->json($data);
    }
}
