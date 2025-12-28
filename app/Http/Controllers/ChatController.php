<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageEvent;
use App\Http\Resources\ChatMessageResource;
use App\Http\Resources\ChatResource;
use App\Http\Resources\SearchChatUserResource;
use App\Http\Resources\SentMessageResource;
use App\Http\Resources\ShowChatResource;
use App\Models\Chat;
use App\Models\ChatBlock;
use App\Models\ChatMember;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function __construct(
        private ChatService $chatService
    ) {}

    public function index(Request $request)
    {
        $auth = auth()->user();

        $chats = Chat::query()
            ->whereRelation('members', 'user_id', $auth->id)
            ->when($request->search, function($query, $search) {
                $query->whereHas('members.user', function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%");
                });
            })
            ->with([
                'members' => function ($query) use ($auth) {
                    $query->where('user_id', '!=', $auth->id);
                    $query->with('user:id,name,avatar');
                },
                'lastMessage' => function ($query) use ($auth) {
                    $query->select('chat_messages.*')
                        ->addSelect(DB::raw("CASE WHEN user_id = {$auth->id} THEN TRUE ELSE FALSE END as is_mine"))
                        ->withCount([
                            'statuses as is_readed' => function ($sub) use ($auth) {
                                $sub->where('user_id', $auth->id)
                                    ->whereNotNull('received_at')
                                    ->whereNotNull('read_at');
                            },
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

        $this->chatService->updateUnreadMessages($auth, $chat);

        $chat->load('blocker');

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
            ->latest('id')
            ->paginate(20);

        $messages->setCollection($messages->getCollection()->reverse());

        return Inertia::render('Chat/Index', [
            'messages' => ChatMessageResource::collection($messages),
            'chat' => new ShowChatResource($chat, $auth),
        ]);
    }

    public function getOlderMessages($chat_id)
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
            ->where('chat_id', $chat_id)
            ->latest('id')
            ->paginate(20);

        $messages->setCollection($messages->getCollection()->reverse());

        return ChatMessageResource::collection($messages);
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

        [$chat, $message] = $this->chatService->new($auth, $members, $request);
        $chat->load([
            'members' => function ($query) use ($auth) {
                $query->where('user_id', '!=', $auth->id);
                $query->with('user:id,name,avatar');
            },
        ]);

        $newMessage = new SentMessageResource($message->load('user:id,name,avatar'));
        $recipient = collect($chat->members)->firstWhere('user_id', '!=', $auth->id);
        broadcast(new ChatMessageEvent($recipient->user_id, $chat->id, $newMessage))->toOthers();

        return response()->json([
            'chat' => new ChatResource($chat),
            'message' => 'Chat created successfully.',
        ], 201);
    }

    public function storeChatMessage(Request $request, Chat $chat)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);
        $auth = auth()->user();

        $chat->load(['members:id,user_id,chat_id']);
        $recipient = collect($chat->members)->firstWhere('user_id', '!=', $auth->id);

        if($this->chatService->isBlocked($auth, $chat->id, $recipient)) {
            throw ValidationException::withMessages([
                'message' => 'Chatting with this user is not available.',
            ]);
        }

        $message = $chat->messages()->create([
            'user_id' => $auth->id,
            'message' => $request->message,
        ]);

        $newMessage = new SentMessageResource($message->load('user:id,name,avatar'));

        broadcast(new ChatMessageEvent($recipient->user_id, $chat->id, $newMessage))->toOthers();

        $recipients = $this->chatService->getSubsribedUsers($chat->id);

        $this->chatService->storeMessageStatus($auth, $chat, $message, $recipients);

        return response()->json([
            'chat_id' => $chat->id,
            'message' => $newMessage,
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

    public function blockUser(Request $request)
    {
        $auth = auth()->user();
        $user_id = $request->user_id;
        $chat_id = $request->chat_id;

        $block = ChatBlock::where([
            'chat_id' => $chat_id,
            'blocker_user_id' => $auth->id,
            'blocked_user_id' => $user_id,
        ]);

        if ($block->exists()) {
            $block->delete();
        } else {
            ChatBlock::create([
                'chat_id' => $chat_id,
                'blocker_user_id' => $auth->id,
                'blocked_user_id' => $user_id,
            ]);
        }

        return response()->json([
            'message' => 'User blocked successfully.',
        ], 201);
    }
}
