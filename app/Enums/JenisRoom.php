<?php

namespace App\Enums;

enum JenisRoom: string
{
    case PHOTO_STUDIO = 'photo_studio';
    case PHOTO_BOOTH = 'photo_booth';

    public function label(): string
    {
        return match ($this) {
            self::PHOTO_STUDIO => 'Photo Studio',
            self::PHOTO_BOOTH => 'Photo Booth',
        };
    }

    public function isBookable(): bool
    {
        return $this === self::PHOTO_STUDIO;
    }

    public function isPhotoBooth(): bool
    {
        return $this === self::PHOTO_BOOTH;
    }
}
