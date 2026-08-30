<?php

namespace App\Policies;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Loan $loan): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isStudent() && $loan->murid_id === $user->student_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Loan $loan): bool
    {
        if (! $user->isStaff()) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Loan $loan): bool
    {
        if (! $user->isStaff()) {
            return false;
        }

        return in_array($loan->status, [
            LoanStatus::PENDING,
            LoanStatus::CANCELLED,
        ]);
    }

    public function approve(User $user, Loan $loan): bool
    {
        return $user->isStaff() && $loan->status === LoanStatus::PENDING;
    }

    public function reject(User $user, Loan $loan): bool
    {
        return $user->isStaff() && $loan->status === LoanStatus::PENDING;
    }

    public function checkout(User $user, Loan $loan): bool
    {
        return $user->isStaff() && $loan->status === LoanStatus::APPROVED;
    }

    public function returnLoan(User $user, Loan $loan): bool
    {
        return $user->isStaff() && $loan->status === LoanStatus::BORROWED;
    }

    public function cancel(User $user, Loan $loan): bool
    {
        if ($user->isStudent() && $loan->murid_id === $user->student_id) {
            return in_array($loan->status, [
                LoanStatus::PENDING,
                LoanStatus::APPROVED,
            ]);
        }

        return false;
    }
}
