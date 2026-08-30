<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Student $student): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isStudent() && $user->student_id === $student->id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Student $student): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->isStaff();
    }
}
