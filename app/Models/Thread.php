<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Thread extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topic(): HasOne
    {
        return $this->hasOne(ThreadTopic::class, 'id', 'thread_topic_id');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ThreadComment::class);
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
