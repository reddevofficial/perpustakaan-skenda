<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Book;
use App\Models\Reservation;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Notifications\LoanStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ReservationService
{
    public function request(Student $student, Book $book, ?string $notes = null): Reservation
    {
        return DB::transaction(function () use ($student, $book, $notes): Reservation {
            $book = Book::query()->lockForUpdate()->findOrFail($book->id);

            if ($book->status !== 'active' || $book->available_stock > 0) {
                throw new RuntimeException('Reservasi hanya dapat dibuat saat stok buku habis.');
            }

            $alreadyReserved = $book->reservations()
                ->where('student_id', $student->id)
                ->whereIn('status', [ReservationStatus::WAITING->value, ReservationStatus::AVAILABLE->value])
                ->exists();

            if ($alreadyReserved) {
                throw new RuntimeException('Anda sudah memiliki reservasi aktif untuk buku ini.');
            }

            $queueNumber = (int) $book->reservations()
                ->whereIn('status', [ReservationStatus::WAITING->value, ReservationStatus::AVAILABLE->value])
                ->max('queue_number') + 1;

            $reservation = $book->reservations()->create([
                'reservation_code' => 'RS-'.strtoupper(Str::random(10)),
                'student_id' => $student->id,
                'queue_number' => $queueNumber,
                'status' => ReservationStatus::WAITING,
                'reserved_at' => now(),
                'notes' => $notes,
            ]);

            return $reservation->load(['book', 'student.user']);
        });
    }

    public function cancel(Reservation $reservation, Student $student): Reservation
    {
        if ($reservation->student_id !== $student->id) {
            throw new RuntimeException('Anda tidak dapat membatalkan reservasi siswa lain.');
        }

        if (! in_array($reservation->status, [ReservationStatus::WAITING, ReservationStatus::AVAILABLE], true)) {
            throw new RuntimeException('Reservasi ini sudah tidak dapat dibatalkan.');
        }

        $reservation->update(['status' => ReservationStatus::CANCELLED]);

        return $reservation->fresh(['book', 'student.user']);
    }

    public function activateNext(Book $book): ?Reservation
    {
        return DB::transaction(function () use ($book): ?Reservation {
            $reservation = $book->reservations()
                ->with('student.user')
                ->where('status', ReservationStatus::WAITING->value)
                ->orderBy('queue_number')
                ->orderBy('reserved_at')
                ->lockForUpdate()
                ->first();

            if (! $reservation) {
                return null;
            }

            $hours = (int) SystemSetting::getValue('reservation_pickup_hours', 48);
            $reservation->update([
                'status' => ReservationStatus::AVAILABLE,
                'expires_at' => now()->addHours($hours),
            ]);

            $reservation = $reservation->fresh(['book', 'student.user']);
            $reservation->student->user?->notify(new LoanStatusNotification(
                'reservation_available',
                'Reservasi '.$reservation->reservation_code.' sudah tersedia untuk diambil.',
            ));

            return $reservation;
        });
    }

    public function expire(): int
    {
        return Reservation::query()
            ->where('status', ReservationStatus::AVAILABLE->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => ReservationStatus::EXPIRED]);
    }
}
