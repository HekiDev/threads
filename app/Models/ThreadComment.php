<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ThreadComment extends Model
{
    /** @use HasFactory<\Database\Factories\ThreadCommentFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(Thread::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ThreadCommentReply::class, 'thread_comment_id', 'id');
    }

    public function subReplies(): HasMany
    {
        return $this->hasMany(ThreadCommentReply::class, 'thread_comment_id', 'id')->where('reply_id', '<>', null);
    }
}
