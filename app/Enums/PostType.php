<?php

namespace App\Enums;

enum PostType: string
{
    case PHOTO = 'image';
    case VIDEO = 'video';
    case GIF = 'gif';
    case REEL = 'reel';

    public function label(): string
    {
        return match ($this) {
            self::PHOTO => 'عکس',
            self::VIDEO => 'ویدیو',
            self::GIF => 'GIF',
            self::REEL => 'ریلز',
        };
    }
}
