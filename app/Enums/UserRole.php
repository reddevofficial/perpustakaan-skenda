<?php

namespace App\Enums;

enum UserRole: string
{
    case STUDENT = 'student';
    case STAFF = 'staff';
    case SUPER_ADMIN = 'super_admin';

    public function label(): string
    {
        return match ($this) {
            self::STUDENT => 'Siswa',
            self::STAFF => 'Petugas',
            self::SUPER_ADMIN => 'Super Admin',
        };
    }

    public function roleId(): int
    {
        return match ($this) {
            self::STUDENT => 7,
            self::STAFF => 6,
            self::SUPER_ADMIN => 1,
        };
    }
}
