<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommentReaction extends Model
{
    /** @use HasFactory<\Database\Factories\CommentReactionFactory> */
    use HasFactory;

    protected $guarded = [];

    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function userable(): MorphTo
    {
        return $this->morphTo();
    }
}
