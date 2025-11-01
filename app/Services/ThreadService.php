<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\ThreadUserResource;
use App\Models\CommentReaction;
use App\Models\Thread;
use App\Models\ThreadComment;
use App\Models\ThreadCommentReply;
use App\Models\ThreadView;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

class ThreadService
{
    public function storeAttachments($request, $thread)
    {
        $fileStorage = 'thread-attachments';
        if (is_array($request->attachments) && count($request->attachments)) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store($fileStorage);
                $url = Storage::url($path);
                $ext = strtolower($file->getClientOriginalExtension());
                $thread->attachments()->create([
                    'url' => $url,
                    'file_path' => $path,
                    'file_extension' => $file->getClientOriginalExtension(),
                    'file_name' => $file->getClientOriginalName(),
                    'type' => in_array($ext, ['jpg', 'jpeg', 'png']) ? 'media' : 'file',
                    'userable_id' => $thread->user_id,
                    'userable_type' => User::class,
                ]);
            }
        }
    }

    public function getCounts($user)
    {
        $totalThreads = Thread::where('user_id', $user->id)->count();
        $totalReactions = CommentReaction::whereRelation('reactable', 'user_id', $user->id)->count();
        return [Number::abbreviate($totalThreads), Number::abbreviate($totalReactions)];
    }

    public function getFollowing($user)
    {
        return $user->following()->limit(10)->get()
            ->toResourceCollection(ThreadUserResource::class);
    }

    public function getFollowers($user)
    {
        return $user->followers()->limit(10)->get()
            ->toResourceCollection(ThreadUserResource::class);
    }

    public function getThreads($user)
    {
        return Thread::query()
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
                    $query->select('id', 'name', 'avatar');
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
    }

    public function getThread($uuid, $user)
    {
        return Thread::query()
            ->withCount('comments')
            ->withCount('reactions')
            ->withCount('views')
            ->withExists(['reactions as reacted' => function ($query) use ($user) {
                $query->where([
                    'userable_id' => $user->id,
                    'userable_type' => User::class,
                ]);
            }])
            ->with([
                'user' => function ($query) use ($user) {
                    $query->select('id', 'name', 'avatar');
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
            ->toResource();
    }

    public function getComments($thread, $sorting, $commentLimit, $subReplyLimit, $user)
    {
        return ThreadComment::query()
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
                    $query->select('id', 'name', 'avatar');
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
                            $query->select('id', 'name', 'avatar');
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
    }

    public function getMoreReplies($comment_id, $limit, $user)
    {
        return ThreadCommentReply::query()
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
                    $query->select('id', 'name', 'avatar');
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
    }

    public function addThreadView($auth_id, $thread_user_id, $thread_id): void
    {
        if ($auth_id === $thread_user_id) return;

        ThreadView::firstOrCreate(['user_id' => $thread_user_id, 'thread_id' => $thread_id]);
    }
}
