<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Extension;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\Student;
use App\Observers\ActivityObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ([Book::class, BookCopy::class, Category::class, Extension::class, Fine::class, Loan::class, Reservation::class, Student::class] as $model) {
            $model::observe(ActivityObserver::class);
        }

        Gate::define('manage-loans', function ($user): bool {
            return in_array($user->role, [UserRole::STAFF, UserRole::SUPER_ADMIN], true);
        });

        Gate::before(function ($user, $ability) {
            if (! $user) {
                return null;
            }

            if (method_exists($user, 'hasRole') && $user->hasRole(UserRole::SUPER_ADMIN->value)) {
                return true;
            }

            return null;
        });
    }
}
