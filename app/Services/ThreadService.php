<?php

declare(strict_types=1);

namespace App\Services;

use App\Http\Resources\ThreadUserResource;
use App\Models\CommentReaction;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

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
        return [format_count($totalThreads), format_count($totalReactions)];
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
}
