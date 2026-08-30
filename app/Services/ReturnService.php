<?php

namespace App\Services;

use App\Enums\BookCopyStatus;
use App\Enums\LoanItemStatus;
use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;

class ReturnService
{
    public function __construct(
        protected FineService $fineService,
        protected NotificationService $notificationService,
    ) {}

    public function returnItem(LoanItem $item): void
    {
        DB::transaction(function () use ($item) {
            if ($item->status === LoanItemStatus::RETURNED) {
                throw new \Exception('Buku sudah dikembalikan sebelumnya.');
            }

            $item->update([
                'status' => LoanItemStatus::RETURNED,
                'returned_at' => now(),
            ]);

            $item->bookCopy->update(['status' => BookCopyStatus::AVAILABLE]);

            $loan = $item->loan;
            $allReturned = $loan->items()->where('status', '!=', LoanItemStatus::RETURNED)->doesntExist();

            if ($allReturned) {
                $loan->update([
                    'status' => LoanStatus::RETURNED,
                    'returned_at' => now(),
                ]);

                if ($loan->due_at && $loan->returned_at->gt($loan->due_at)) {
                    $this->fineService->calculateForLoan($loan);
                }
            }

            AuditLog::log('book_returned', $item, null, [
                'returned_at' => now()->toISOString(),
                'loan_fully_returned' => $allReturned,
            ]);

            if ($allReturned) {
                $this->notificationService->sendLoanStatusUpdate($loan, 'loan_returned');
            }

            $this->checkReservations($item->bookCopy->book_id);
        });
    }

    public function processReturn(Loan $loan): void
    {
        foreach ($loan->items as $item) {
            if ($item->status !== LoanItemStatus::RETURNED) {
                $this->returnItem($item);
            }
        }
    }

    protected function checkReservations(int $bookId): void
    {
        $reservation = Reservation::where('book_id', $bookId)
            ->where('status', ReservationStatus::WAITING)
            ->orderBy('queue_position')
            ->first();

        if ($reservation) {
            $expiryDays = (int) setting('reservation_expiry_days', 2);
            $reservation->update([
                'status' => ReservationStatus::AVAILABLE,
                'expires_at' => now()->addDays($expiryDays),
            ]);

            app(NotificationService::class)->sendReservationAvailable($reservation);
        }
    }
}
