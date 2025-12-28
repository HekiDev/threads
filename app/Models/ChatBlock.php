<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBlock extends Model
{
    /** @use HasFactory<\Database\Factories\ChatBlockFactory> */
    use HasFactory;

    protected $guarded = [];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function blocked(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocker_user_id');
    }
}
