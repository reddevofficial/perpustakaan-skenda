<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('filament.admin.auth.login');
        }

        return match ($user->role) {
            UserRole::SUPER_ADMIN => redirect()->to('/admin'),
            UserRole::STAFF => redirect()->to('/staff'),
            UserRole::STUDENT => redirect()->to('/siswa'),
            default => redirect()->route('access.denied'),
        };
    }
}
