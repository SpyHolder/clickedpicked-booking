<?php

namespace App\Enums;

enum EditStatus: string
{
    case MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    case PENDING = 'pending';
    case DIPROSES = 'diproses';
    case SELESAI = 'selesai';
    case DIKIRIM = 'dikirim';
    case DITOLAK = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::MENUNGGU_VERIFIKASI => 'Menunggu verifikasi',
            self::PENDING => 'Antre',
            self::DIPROSES => 'Diproses',
            self::SELESAI => 'Selesai',
            self::DIKIRIM => 'Terkirim',
            self::DITOLAK => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::MENUNGGU_VERIFIKASI => 'amber',
            self::PENDING => 'slate',
            self::DIPROSES => 'sky',
            self::SELESAI => 'teal',
            self::DIKIRIM => 'emerald',
            self::DITOLAK => 'red',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::MENUNGGU_VERIFIKASI => in_array($target, [self::PENDING, self::DITOLAK]),
            self::PENDING => in_array($target, [self::DIPROSES, self::DITOLAK]),
            self::DIPROSES => in_array($target, [self::SELESAI]),
            self::SELESAI => in_array($target, [self::DIKIRIM]),
            self::DIKIRIM => in_array($target, [self::DIPROSES]),
            self::DITOLAK => false,
        };
    }
}