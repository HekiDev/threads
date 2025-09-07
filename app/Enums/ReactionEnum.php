<?php

namespace App\Enums;

enum ReactionEnum: string
{
    case Like = 'like';
    case Heart = 'heart';
    case Haha = 'haha';
    case Wow = 'wow';
    case Sad = 'sad';
    case Angry = 'angry';
    case Confused = 'confused';
    case Surprised = 'surprised';

    /**
     * Get all enum values as an array.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
