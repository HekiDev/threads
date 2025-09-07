<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ThreadCommentReply extends Model
{
    /** @use HasFactory<\Database\Factories\ThreadCommentReplyFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function mainComment(): BelongsTo
    {
        return $this->belongsTo(ThreadComment::class, 'thread_comment_id', 'id');
    }

    public function mainReply(): BelongsTo
    {
        return $this->belongsTo(ThreadCommentReply::class, 'reply_id', 'id');
    }

    public function subReplies(): HasMany
    {
        return $this->hasMany(ThreadCommentReply::class, 'reply_id', 'id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(CommentReaction::class, 'reactable');
    }

    public function createOrDeleteReaction($relation, array $attributes)
    {
        $existing = $relation->where($attributes)->first();

        if ($existing) {
            $existing->delete();
            return null; // deleted
        }

        return $relation->create($attributes); // created
    }
}
