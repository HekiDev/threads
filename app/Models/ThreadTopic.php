<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreadTopic extends Model
{
    /** @use HasFactory<\Database\Factories\ThreadTopicFactory> */
    use HasFactory;

    protected $guarded = [];

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }
}
