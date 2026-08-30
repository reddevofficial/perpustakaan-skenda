<?php

namespace App\Policies;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Reservation $reservation): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isStudent() && $reservation->murid_id === $user->student_id) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, Reservation $reservation): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Reservation $reservation): bool
    {
        if (! $user->isStaff()) {
            return false;
        }

        return in_array($reservation->status, [
            ReservationStatus::WAITING,
            ReservationStatus::CANCELLED,
        ]);
    }

    public function fulfill(User $user, Reservation $reservation): bool
    {
        return $user->isStaff() && $reservation->status === ReservationStatus::AVAILABLE;
    }

    public function cancel(User $user, Reservation $reservation): bool
    {
        if ($user->isStudent() && $reservation->murid_id === $user->student_id) {
            return in_array($reservation->status, [
                ReservationStatus::WAITING,
                ReservationStatus::AVAILABLE,
            ]);
        }

        if ($user->isStaff()) {
            return in_array($reservation->status, [
                ReservationStatus::WAITING,
                ReservationStatus::AVAILABLE,
            ]);
        }

        return false;
    }
}
