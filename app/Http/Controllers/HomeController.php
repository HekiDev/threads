<?php

namespace App\Http\Controllers;

use App\Http\Requests\ThreadCommentRequest;
use App\Http\Requests\ThreadRequest;
use App\Models\Thread;
use App\Models\ThreadComment;
use App\Models\ThreadCommentReply;
use App\Models\ThreadTopic;
use App\Models\User;
use App\Services\ThreadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __construct(
        private ThreadService $threadService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'by_followers' => $request->boolean('by_followers', false),
            'by_following' => $request->boolean('by_following', false),
        ];

        $user = auth()->user();
        $totalThreads = Thread::where('user_id', $user->id)->count();
        $totalReactions = 0;

        $data = Thread::query()
            ->withCount('comments')
            ->with([
                'user:id,name',
                'topic:id,name',
                'attachments:id,url,type,attachable_id,attachable_type',
            ])
            ->latest()
            ->paginate(10)
            ->toResourceCollection();

        return Inertia::render('Dashboard', [
            'threads' => Inertia::deepMerge($data),
            'currentPage' => $data->currentPage(),
            'lastPage' => $data->lastPage(),
            'filters' => $filters,
            'totalThreads' => $totalThreads,
            'totalReactions' => $totalReactions,
        ]);
    }

    public function show($username, $uuid)
    {
        $commentLimit = 15;
        $subReplyLimit = 3;

        $thread = Thread::query()
            ->withCount('comments')
            ->with([
                'user:id,name',
                'topic:id,name',
                'attachments:id,url,type,attachable_id,attachable_type',
            ])
            ->where('uuid', $uuid)
            ->firstOrFail()
            ->toResource()
            ->additional([
                'views_count' => 0,
            ]);

        $comments = ThreadComment::query()
            ->withCount('replies')
            ->with([
                'user:id,name',
                'attachments:id,url,type,attachable_id,attachable_type',
                'replies' => function ($query) use ($subReplyLimit) {
                    $query->withCount('subReplies');
                    $query->with([
                        'mainReply.user:id,name',
                        'user:id,name',
                        'attachments:id,url,type,attachable_id,attachable_type',
                    ])->latest()->limit($subReplyLimit);
                },
            ])
            ->where('thread_id', $thread->id)
            ->latest()
            ->paginate($commentLimit)
            ->toResourceCollection();

        return Inertia::render('Thread/Show', [
            'post' => $thread,
            'comments' => Inertia::deepMerge($comments),
            'subReplyLimit' => $subReplyLimit,
        ]);
    }

    public function storeThread(ThreadRequest $request)
    {
        $topic = null;
        if ($request->topic) {
            $topic = ThreadTopic::firstOrCreate(['name' => $request->topic]);
        }

        $thread = Thread::create([
            'uuid' => Str::uuid(),
            'description' => $request->description,
            'thread_topic_id' => $topic?->id,
            'user_id' => auth()->user()->id,
        ]);

        $this->threadService->storeAttachments($request, $thread);

        return response()->json(['message' => 'Thread created successfully.']);
    }

    public function storeComment(ThreadCommentRequest $request, $uuid)
    {
        $thread = Thread::where('uuid', $uuid)->firstOrFail();
        $thread->comments()->create([
            'user_id' => auth()->user()->id,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Comment submitted successfully.']);
    }

    public function storeCommentReply(ThreadCommentRequest $request, ThreadComment $comment)
    {
        $reply = ThreadCommentReply::create([
            'thread_comment_id' => $comment->id,
            'comment' => $request->comment,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json(['message' => 'Comment submitted successfully.']);
    }

    public function storeCommentSubReply(ThreadCommentRequest $request, ThreadCommentReply $reply)
    {
        ThreadCommentReply::create([
            'thread_comment_id' => $reply->thread_comment_id,
            'reply_id' => $reply->id,
            'comment' => $request->comment,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json(['message' => 'Comment submitted successfully.']);
    }
}
