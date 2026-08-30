<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    public function create(Student $student, Book $book): Reservation
    {
        return DB::transaction(function () use ($student, $book) {
            $existing = Reservation::where('murid_id', $student->id)
                ->where('book_id', $book->id)
                ->whereIn('status', [ReservationStatus::WAITING, ReservationStatus::AVAILABLE])
                ->first();

            if ($existing) {
                throw new \Exception('Anda sudah memiliki reservasi aktif untuk buku ini.');
            }

            $queuePosition = Reservation::where('book_id', $book->id)
                ->where('status', ReservationStatus::WAITING)
                ->max('queue_position');

            $reservation = Reservation::create([
                'reservation_number' => Reservation::generateReservationNumber(),
                'murid_id' => $student->id,
                'book_id' => $book->id,
                'status' => ReservationStatus::WAITING,
                'reserved_at' => now(),
                'queue_position' => ($queuePosition ?? 0) + 1,
            ]);

            AuditLog::log('reservation_created', $reservation, null, $reservation->toArray());

            return $reservation;
        });
    }

    public function cancel(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'status' => ReservationStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);

            AuditLog::log('reservation_cancelled', $reservation, null, ['status' => ReservationStatus::CANCELLED->value]);
        });
    }

    public function fulfill(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'status' => ReservationStatus::FULFILLED,
                'fulfilled_at' => now(),
            ]);

            AuditLog::log('reservation_fulfilled', $reservation, null, ['status' => ReservationStatus::FULFILLED->value]);
        });
    }

    public function expireReservations(): void
    {
        $expired = Reservation::where('status', ReservationStatus::AVAILABLE)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $reservation) {
            $reservation->update(['status' => ReservationStatus::EXPIRED]);

            $next = Reservation::where('book_id', $reservation->book_id)
                ->where('status', ReservationStatus::WAITING)
                ->orderBy('queue_position')
                ->first();

            if ($next) {
                $next->update([
                    'status' => ReservationStatus::AVAILABLE,
                    'expires_at' => now()->addDays(2),
                ]);
            }
        }
    }
}
