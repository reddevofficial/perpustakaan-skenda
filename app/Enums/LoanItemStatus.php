<?php

namespace App\Enums;

enum LoanItemStatus: string
{
    case PENDING = 'pending';
    case BORROWED = 'borrowed';
    case RETURNED = 'returned';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu',
            self::BORROWED => 'Dipinjam',
            self::RETURNED => 'Dikembalikan',
            self::OVERDUE => 'Terlambat',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::BORROWED => 'info',
            self::RETURNED => 'success',
            self::OVERDUE => 'danger',
        };
    }
}
