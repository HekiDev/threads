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
        [$totalThreads, $totalReactions] = $this->threadService->getCounts($user);

        $data = Thread::query()
            ->withCount('comments')
            ->withCount('reactions')
            ->withExists(['reactions as reacted' => function ($query) use ($user) {
                $query->where([
                    'userable_id' => $user->id,
                    'userable_type' => User::class,
                ]);
            }])
            ->with([
                'user' => function ($query) use ($user) {
                    $query->select('id', 'name');
                    $query->withExists([
                        'followers as followed' => function ($query) use ($user) {
                            $query->where('follower_id', $user->id);
                        }
                    ]);
                },
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
            'following' => $this->threadService->getFollowing($user),
            'followers' => $this->threadService->getFollowers($user),
        ]);
    }

    public function show($username, $uuid)
    {
        $commentLimit = 15;
        $subReplyLimit = 3;
        $user = auth()->user();
        $sorting = request('sorting', 'top');

        [$totalThreads, $totalReactions] = $this->threadService->getCounts($user);

        $thread = Thread::query()
            ->withCount('comments')
            ->withCount('reactions')
            ->withExists(['reactions as reacted' => function ($query) use ($user) {
                $query->where([
                    'userable_id' => $user->id,
                    'userable_type' => User::class,
                ]);
            }])
            ->with([
                'user' => function ($query) use ($user) {
                    $query->select('id', 'name');
                    $query->withExists([
                        'followers as followed' => function ($query) use ($user) {
                            $query->where('follower_id', $user->id);
                        }
                    ]);
                },
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
            ->withCount('reactions')
            ->withExists(['reactions as reacted' => function ($query) use ($user) {
                $query->where([
                    'userable_id' => $user->id,
                    'userable_type' => User::class,
                ]);
            }])
            ->with([
                'user' => function ($query) use ($user) {
                    $query->select('id', 'name');
                    $query->withExists([
                        'followers as followed' => function ($query) use ($user) {
                            $query->where('follower_id', $user->id);
                        }
                    ]);
                },
                'attachments:id,url,type,attachable_id,attachable_type',
                'replies' => function ($query) use ($subReplyLimit, $user) {
                    $query->withCount('subReplies');
                    $query->withCount('reactions');
                    $query->withExists(['reactions as reacted' => function ($query) use ($user) {
                        $query->where([
                            'userable_id' => $user->id,
                            'userable_type' => User::class,
                        ]);
                    }]);
                    $query->with([
                        'mainReply.user:id,name',
                        'user' => function ($query) use ($user) {
                            $query->select('id', 'name');
                            $query->withExists([
                                'followers as followed' => function ($query) use ($user) {
                                    $query->where('follower_id', $user->id);
                                }
                            ]);
                        },
                        'attachments:id,url,type,attachable_id,attachable_type',
                    ])->latest()->limit($subReplyLimit);
                },
            ])
            ->where('thread_id', $thread->id)
            ->when(! $sorting || $sorting === 'top', function ($query) {
                $query->orderByDesc('replies_count');
            }, function ($query) {
                $query->latest();
            })
            ->paginate($commentLimit)
            ->toResourceCollection();

        return Inertia::render('Thread/Show', [
            'post' => $thread,
            'comments' => Inertia::deepMerge($comments),
            'sorting' => $sorting,
            'subReplyLimit' => $subReplyLimit,
            'totalThreads' => $totalThreads,
            'totalReactions' => $totalReactions,
            'following' => $this->threadService->getFollowing($user),
            'followers' => $this->threadService->getFollowers($user),
        ]);
    }

    public function getMoreReplies($comment_id)
    {
        $user = auth()->user();
        $limit = 3;

        $replies = ThreadCommentReply::query()
            ->where('thread_comment_id', $comment_id)
            ->withCount('subReplies')
            ->withCount('reactions')
            ->withExists(['reactions as reacted' => function ($query) use ($user) {
                $query->where([
                    'userable_id' => $user->id,
                    'userable_type' => User::class,
                ]);
            }])
            ->with([
                'mainReply.user:id,name',
                'user' => function ($query) use ($user) {
                    $query->select('id', 'name');
                    $query->withExists([
                        'followers as followed' => function ($query) use ($user) {
                            $query->where('follower_id', $user->id);
                        }
                    ]);
                },
                'attachments:id,url,type,attachable_id,attachable_type',
            ])
            ->latest()
            ->paginate($limit)
            ->toResourceCollection();

        return response()->json([
            'data' => $replies,
            'currentPage' => $replies->currentPage(),
            'lastPage' => $replies->lastPage(),
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
        ThreadCommentReply::create([
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

    public function storeReaction($uuid)
    {
        $thread = Thread::where('uuid', $uuid)->firstOrFail();
        $thread->createOrDeleteReaction($thread->reactions(),
            [
                'type' => request('reaction'),
                'userable_id' => auth()->id(),
                'userable_type' => User::class,
            ]
        );

        return response()->json(['message' => 'Reaction submitted successfully.']);
    }

    public function storeCommentReaction(ThreadComment $comment)
    {
        $comment->createOrDeleteReaction($comment->reactions(),
            [
                'type' => request('reaction'),
                'userable_id' => auth()->id(),
                'userable_type' => User::class,
            ]
        );

        return response()->json(['message' => 'Reaction submitted successfully.']);
    }

    public function storeCommentSubReplyReaction(ThreadCommentReply $reply)
    {
        $reply->createOrDeleteReaction($reply->reactions(),
            [
                'type' => request('reaction'),
                'userable_id' => auth()->id(),
                'userable_type' => User::class,
            ]
        );

        return response()->json(['message' => 'Reaction submitted successfully.']);
    }

    public function followUser(User $user)
    {
        $authUser = auth()->user();

        if ($authUser->id === $user->id) {
            return response()->json(['message' => 'You cannot follow yourself.'], 422);
        }

        $isFollowed = $authUser->following()->where('followed_id', $user->id)->exists();

        if (! $isFollowed) {
            $authUser->following()->attach($user->id);
            return response()->json(['message' => 'You are now following ' . $user->name]);
        } else {
            $authUser->following()->detach($user->id);
            return response()->json(['message' => 'You have unfollowed ' . $user->name]);
        }
    }
}
