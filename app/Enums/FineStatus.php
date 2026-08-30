<?php

namespace App\Enums;

enum FineStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case WAIVED = 'waived';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Dibayar',
            self::PAID => 'Sudah Dibayar',
            self::WAIVED => 'Dibebaskan',
        };
    }

    public function color(): string|array|null
    {
        return match ($this) {
            self::UNPAID => 'danger',
            self::PAID => 'success',
            self::WAIVED => 'gray',
        };
    }
}
