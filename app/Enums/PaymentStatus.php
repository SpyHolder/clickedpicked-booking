<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::PAID => 'Lunas',
            self::FAILED => 'Gagal',
            self::REFUNDED => 'Dikembalikan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'amber',
            self::PAID => 'emerald',
            self::FAILED => 'red',
            self::REFUNDED => 'slate',
        };
    }
}