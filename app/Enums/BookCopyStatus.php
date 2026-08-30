<?php

namespace App\Enums;

enum BookCopyStatus: string
{
    case AVAILABLE = 'available';
    case BORROWED = 'borrowed';
    case RESERVED = 'reserved';
    case LOST = 'lost';
    case DAMAGED = 'damaged';
    case MAINTENANCE = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Tersedia',
            self::BORROWED => 'Dipinjam',
            self::RESERVED => 'Direservasi',
            self::LOST => 'Hilang',
            self::DAMAGED => 'Rusak',
            self::MAINTENANCE => 'Perawatan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::BORROWED => 'info',
            self::RESERVED => 'warning',
            self::LOST => 'danger',
            self::DAMAGED => 'danger',
            self::MAINTENANCE => 'gray',
        };
    }
}
