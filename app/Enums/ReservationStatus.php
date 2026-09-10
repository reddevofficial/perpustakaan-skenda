<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case WAITING = 'waiting';
    case AVAILABLE = 'available';
    case PICKED_UP = 'picked_up';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
}
