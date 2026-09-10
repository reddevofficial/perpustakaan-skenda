<?php

namespace App\Enums;

enum BookCopyStatus: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case BORROWED = 'borrowed';
    case DAMAGED = 'damaged';
    case LOST = 'lost';
    case INACTIVE = 'inactive';
}
