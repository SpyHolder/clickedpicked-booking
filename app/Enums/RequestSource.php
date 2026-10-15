<?php

namespace App\Enums;

enum RequestSource: string
{
    case QR = 'qr';
    case ADMIN = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::QR => 'QR Code',
            self::ADMIN => 'Input Admin',
        };
    }
}