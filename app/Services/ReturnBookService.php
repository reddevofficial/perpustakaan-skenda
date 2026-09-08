<?php

namespace App\Services;

use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReturnBookService
{
    public function return(Loan $loan, FineService $fineService, ReservationService $reservationService, ?string $notes = null): Loan
    {
        return DB::transaction(function () use ($loan, $fineService, $reservationService, $notes): Loan {
            $loan = Loan::query()->with('items.bookCopy.book')->lockForUpdate()->findOrFail($loan->id);

            if (! in_array($loan->status, [LoanStatus::BORROWED, LoanStatus::OVERDUE], true)) {
                throw new RuntimeException('Peminjaman ini tidak sedang dipinjam.');
            }

            foreach ($loan->items as $item) {
                $copy = $item->bookCopy->fresh();
                if (! $copy || $copy->status !== BookCopyStatus::BORROWED) {
                    throw new RuntimeException('Status eksemplar tidak valid untuk pengembalian.');
                }

                $copy->update(['status' => BookCopyStatus::AVAILABLE]);
                $copy->book()->increment('available_stock');
            }

            $loan->update([
                'status' => LoanStatus::RETURNED,
                'returned_at' => now(),
                'notes' => $notes ?: $loan->notes,
            ]);

            $fineService->sync($loan->fresh());
            $reservationService->activateNext($loan->items->first()->bookCopy->book);

            return $loan->fresh(['items.bookCopy.book', 'student.user']);
        });
    }
}
