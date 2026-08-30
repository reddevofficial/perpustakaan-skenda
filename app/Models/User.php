<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'role_id', 'student_id', 'username',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($panel->getId() === 'admin') {
            return $this->isStaff();
        }

        return true;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role_id === 1;
    }

    public function isStaff(): bool
    {
        return in_array($this->role_id, [1, 5, 6]);
    }

    public function isStudent(): bool
    {
        return $this->role_id === 7;
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->role?->permissions ?? [];

        if (in_array('*', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }
}
