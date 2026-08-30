<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\Loan;
use App\Models\LoanExtension;
use App\Models\Reservation;
use App\Notifications\LibraryNotification;

class NotificationService
{
    public function sendLoanStatusUpdate(Loan $loan, string $type): void
    {
        $student = $loan->student;
        if (! $student || ! $student->user) {
            return;
        }

        $messages = [
            'loan_approved' => 'Peminjaman '.$loan->loan_number.' telah disetujui.',
            'loan_rejected' => 'Peminjaman '.$loan->loan_number.' ditolak.'.($loan->rejection_reason ? ' Alasan: '.$loan->rejection_reason : ''),
            'loan_borrowed' => 'Buku untuk peminjaman '.$loan->loan_number.' telah diterima.',
            'loan_returned' => 'Peminjaman '.$loan->loan_number.' telah dikembalikan.',
            'loan_overdue' => 'Peminjaman '.$loan->loan_number.' telah melewati jatuh tempo.',
            'loan_reminder_h3' => 'Peminjaman '.$loan->loan_number.' akan jatuh tempo dalam 3 hari.',
            'loan_reminder_h1' => 'Peminjaman '.$loan->loan_number.' akan jatuh tempo besok.',
            'loan_reminder_due' => 'Peminjaman '.$loan->loan_number.' jatuh tempo hari ini.',
        ];

        $message = $messages[$type] ?? 'Status peminjaman telah diperbarui.';

        $student->user->notify(new LibraryNotification($message, $type));
    }

    public function sendReservationAvailable(Reservation $reservation): void
    {
        $student = $reservation->student;
        if (! $student || ! $student->user) {
            return;
        }

        $student->user->notify(new LibraryNotification(
            'Buku "'.$reservation->book->title.'" sudah tersedia untuk diambil. Silakan ambil dalam '.setting('reservation_expiry_days', 2).' hari.',
            'reservation_available'
        ));
    }

    public function sendFineCreated(Fine $fine): void
    {
        $student = $fine->student;
        if (! $student || ! $student->user) {
            return;
        }

        $student->user->notify(new LibraryNotification(
            'Anda memiliki denda sebesar Rp'.number_format($fine->amount, 0, ',', '.').'.',
            'fine_created'
        ));
    }

    public function sendExtensionStatus(LoanExtension $extension, string $type): void
    {
        $loan = $extension->loan;
        $student = $loan->student;
        if (! $student || ! $student->user) {
            return;
        }

        $message = match ($type) {
            'extension_approved' => 'Perpanjangan peminjaman '.$loan->loan_number.' telah disetujui.',
            'extension_rejected' => 'Perpanjangan peminjaman '.$loan->loan_number.' ditolak.',
            default => 'Status perpanjangan telah diperbarui.',
        };

        $student->user->notify(new LibraryNotification($message, $type));
    }
}
