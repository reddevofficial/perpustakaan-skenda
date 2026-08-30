<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Auth\Pages\Login;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomLogin extends Login
{
    public function getTitle(): string
    {
        return 'Masuk - Perpustakaan';
    }

    public function getHeading(): string
    {
        return 'Masuk ke Sistem Perpustakaan';
    }

    public function getSubheading(): ?string
    {
        return 'Gunakan email atau NIPD untuk masuk';
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email atau NIPD')
            ->required()
            ->autocomplete()
            ->autofocus()
            ->placeholder('email@contoh.com atau NIPD');
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $identity = $data['email'];
        $password = $data['password'];

        $user = User::where('email', $identity)->first();

        if (! $user) {
            $studentId = \DB::table('murid')->where('nipd', $identity)->value('id');
            if ($studentId) {
                $user = User::where('student_id', $studentId)->first();
            }
        }

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'data.email' => 'Akun Anda tidak aktif.',
            ]);
        }

        $user->update(['last_login_at' => now()]);

        return [
            'email' => $user->email,
            'password' => $password,
        ];
    }
}
