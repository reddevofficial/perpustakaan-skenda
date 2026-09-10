<?php

namespace App\Enums;

enum FineStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
}
