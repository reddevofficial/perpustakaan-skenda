<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'username', 'password', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $guard_name = 'web';

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function routeNotificationForDatabase(): string
    {
        return (string) $this->getKey();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->role === UserRole::SUPER_ADMIN,
            'staff' => in_array($this->role, [UserRole::STAFF, UserRole::SUPER_ADMIN], true),
            'student' => in_array($this->role, [UserRole::STUDENT, UserRole::SUPER_ADMIN], true),
            default => false,
        };
    }

    public function getDefaultPanel(): ?string
    {
        return match ($this->role) {
            UserRole::SUPER_ADMIN => 'admin',
            UserRole::STAFF => 'staff',
            UserRole::STUDENT => 'student',
            default => null,
        };
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'status' => 'string',
        ];
    }
}
