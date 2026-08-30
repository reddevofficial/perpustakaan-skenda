<?php

namespace App\Policies;

use App\Enums\FineStatus;
use App\Models\Fine;
use App\Models\User;

class FinePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Fine $fine): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isStudent() && $fine->murid_id === $user->student_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Fine $fine): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Fine $fine): bool
    {
        if (! $user->isStaff()) {
            return false;
        }

        return $fine->status === FineStatus::UNPAID;
    }

    public function pay(User $user, Fine $fine): bool
    {
        return $user->isStaff() && $fine->status === FineStatus::UNPAID;
    }

    public function waive(User $user, Fine $fine): bool
    {
        return $user->isStaff() && $fine->status === FineStatus::UNPAID;
    }
}
