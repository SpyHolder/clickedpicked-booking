<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::SENT => 'Terkirim',
            self::DELIVERED => 'Diterima',
            self::READ => 'Dibaca',
            self::FAILED => 'Gagal',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'slate',
            self::SENT => 'sky',
            self::DELIVERED => 'emerald',
            self::READ => 'emerald',
            self::FAILED => 'red',
        };
    }
}