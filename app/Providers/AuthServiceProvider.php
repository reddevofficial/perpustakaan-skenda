<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Category;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\Student;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\BookPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\FinePolicy;
use App\Policies\LoanPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\StudentResourcePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Book::class => BookPolicy::class,
        Category::class => CategoryPolicy::class,
        Student::class => StudentResourcePolicy::class,
        Loan::class => LoanPolicy::class,
        Reservation::class => ReservationPolicy::class,
        Fine::class => FinePolicy::class,
        User::class => UserPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
