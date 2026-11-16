<?php

namespace App\Enums;

enum CabangStudio: string
{
    case BATU_LIMA = 'batu_lima';
    case JALAN_MERDEKA = 'jalan_merdeka';

    public function label(): string
    {
        return match ($this) {
            self::BATU_LIMA => 'Batu 5',
            self::JALAN_MERDEKA => 'Jalan Merdeka',
        };
    }

    public function alamat(): string
    {
        return match ($this) {
            self::BATU_LIMA => 'Jl. Merdeka, BT 5 Atas',
            self::JALAN_MERDEKA => 'Jl. Merdeka',
        };
    }
}
