<?php

namespace App\Enums;

enum LoanStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case BORROWED = 'borrowed';
    case RETURNED = 'returned';
    case REJECTED = 'rejected';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Persetujuan',
            self::APPROVED => 'Disetujui',
            self::BORROWED => 'Sedang Dipinjam',
            self::RETURNED => 'Dikembalikan',
            self::REJECTED => 'Ditolak',
            self::OVERDUE => 'Terlambat',
            self::CANCELLED => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'info',
            self::BORROWED => 'info',
            self::RETURNED => 'success',
            self::REJECTED => 'danger',
            self::OVERDUE => 'danger',
            self::CANCELLED => 'gray',
        };
    }
}
