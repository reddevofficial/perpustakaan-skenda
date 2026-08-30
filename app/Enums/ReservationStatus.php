<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case WAITING = 'waiting';
    case AVAILABLE = 'available';
    case FULFILLED = 'fulfilled';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'Antrean',
            self::AVAILABLE => 'Tersedia',
            self::FULFILLED => 'Diambil',
            self::EXPIRED => 'Kedaluwarsa',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WAITING => 'warning',
            self::AVAILABLE => 'success',
            self::FULFILLED => 'success',
            self::EXPIRED => 'gray',
            self::CANCELLED => 'danger',
        };
    }
}
