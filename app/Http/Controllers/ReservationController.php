<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class ReservationController extends Controller
{
    public function store(Book $book, ReservationService $reservationService): RedirectResponse
    {
        $student = Auth::user()?->student;
        abort_unless($student, 403);

        try {
            $reservationService->request($student, $book, request('notes'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reservasi berhasil dibuat.');
    }

    public function cancel(Reservation $reservation, ReservationService $reservationService): RedirectResponse
    {
        $student = Auth::user()?->student;
        abort_unless($student, 403);

        try {
            $reservationService->cancel($reservation, $student);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Reservasi dibatalkan.');
    }
}
