<?php

namespace App\Services;

use App\Enums\BookCopyStatus;
use App\Enums\ExtensionStatus;
use App\Enums\LoanItemStatus;
use App\Enums\LoanStatus;
use App\Enums\ReservationStatus;
use App\Models\AuditLog;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\LoanExtension;
use App\Models\Reservation;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LoanService
{
    public function request(Student $student, array $bookCopyIds): Loan
    {
        return DB::transaction(function () use ($student, $bookCopyIds) {
            $loan = Loan::create([
                'loan_number' => Loan::generateLoanNumber(),
                'murid_id' => $student->id,
                'status' => LoanStatus::PENDING,
                'requested_at' => now(),
            ]);

            foreach ($bookCopyIds as $copyId) {
                $copy = BookCopy::lockForUpdate()->findOrFail($copyId);

                if ($copy->status !== BookCopyStatus::AVAILABLE) {
                    throw new \Exception("Buku {$copy->barcode} tidak tersedia.");
                }

                $loan->items()->create([
                    'book_copy_id' => $copy->id,
                    'status' => LoanItemStatus::PENDING,
                ]);
            }

            AuditLog::log('loan_requested', $loan, null, $loan->toArray());

            return $loan;
        });
    }

    public function approve(Loan $loan, int $approvedBy): void
    {
        DB::transaction(function () use ($loan, $approvedBy) {
            $loan->update([
                'status' => LoanStatus::APPROVED,
                'approved_at' => now(),
                'approved_by' => $approvedBy,
            ]);

            AuditLog::log('loan_approved', $loan, ['status' => $loan->getOriginal('status')], ['status' => LoanStatus::APPROVED->value]);

            app(NotificationService::class)->sendLoanStatusUpdate($loan, 'loan_approved');
        });
    }

    public function reject(Loan $loan, int $rejectedBy, ?string $reason = null): void
    {
        DB::transaction(function () use ($loan, $rejectedBy, $reason) {
            $loan->update([
                'status' => LoanStatus::REJECTED,
                'rejected_by' => $rejectedBy,
                'rejection_reason' => $reason,
            ]);

            AuditLog::log('loan_rejected', $loan, ['status' => $loan->getOriginal('status')], ['status' => LoanStatus::REJECTED->value]);

            app(NotificationService::class)->sendLoanStatusUpdate($loan, 'loan_rejected');
        });
    }

    public function markBorrowed(Loan $loan): void
    {
        DB::transaction(function () use ($loan) {
            $durationDays = (int) setting('loan_duration_days', 7);
            $dueAt = now()->addDays($durationDays);

            $loan->update([
                'status' => LoanStatus::BORROWED,
                'borrowed_at' => now(),
                'due_at' => $dueAt,
            ]);

            foreach ($loan->items as $item) {
                $item->update([
                    'status' => LoanItemStatus::BORROWED,
                    'due_at' => $dueAt,
                ]);
                $item->bookCopy->update(['status' => BookCopyStatus::BORROWED]);
            }

            AuditLog::log('book_borrowed', $loan, null, ['status' => LoanStatus::BORROWED->value]);

            app(NotificationService::class)->sendLoanStatusUpdate($loan, 'loan_borrowed');
        });
    }

    public function cancel(Loan $loan): void
    {
        DB::transaction(function () use ($loan) {
            $loan->update(['status' => LoanStatus::CANCELLED]);
            AuditLog::log('loan_cancelled', $loan, null, ['status' => LoanStatus::CANCELLED->value]);
        });
    }

    public function requestExtension(Loan $loan, User $requestedBy, ?string $reason = null): LoanExtension
    {
        $maxExtensions = (int) setting('max_extensions', 1);
        $extensionCount = $loan->extensions()->count();

        if ($extensionCount >= $maxExtensions) {
            throw new \Exception('Buku telah mencapai batas maksimal perpanjangan.');
        }

        $hasReservations = Reservation::where('book_id', $loan->items->first()?->bookCopy?->book_id)
            ->where('status', ReservationStatus::WAITING)
            ->exists();

        if ($hasReservations) {
            throw new \Exception('Tidak dapat memperpanjang karena buku memiliki antrean reservasi.');
        }

        $extensionDays = (int) setting('extension_days', 7);
        $oldDueAt = $loan->due_at;
        $newDueAt = $oldDueAt->copy()->addDays($extensionDays);

        return DB::transaction(function () use ($loan, $requestedBy, $reason, $oldDueAt, $newDueAt) {
            $extension = LoanExtension::create([
                'loan_id' => $loan->id,
                'requested_by' => $requestedBy->id,
                'old_due_at' => $oldDueAt,
                'new_due_at' => $newDueAt,
                'status' => ExtensionStatus::PENDING,
                'reason' => $reason,
            ]);

            AuditLog::log('extension_requested', $extension, null, $extension->toArray());

            return $extension;
        });
    }

    public function approveExtension(LoanExtension $extension, int $approvedBy): void
    {
        DB::transaction(function () use ($extension, $approvedBy) {
            $extension->update([
                'status' => ExtensionStatus::APPROVED,
                'approved_by' => $approvedBy,
            ]);

            $extension->loan->update(['due_at' => $extension->new_due_at]);
            foreach ($extension->loan->items as $item) {
                $item->update(['due_at' => $extension->new_due_at]);
            }

            AuditLog::log('extension_approved', $extension, ['status' => ExtensionStatus::PENDING->value], ['status' => ExtensionStatus::APPROVED->value]);

            app(NotificationService::class)->sendExtensionStatus($extension, 'extension_approved');
        });
    }

    public function rejectExtension(LoanExtension $extension, int $rejectedBy): void
    {
        DB::transaction(function () use ($extension, $rejectedBy) {
            $extension->update([
                'status' => ExtensionStatus::REJECTED,
                'approved_by' => $rejectedBy,
            ]);

            AuditLog::log('extension_rejected', $extension, ['status' => ExtensionStatus::PENDING->value], ['status' => ExtensionStatus::REJECTED->value]);

            app(NotificationService::class)->sendExtensionStatus($extension, 'extension_rejected');
        });
    }

    public function canExtend(Loan $loan): bool
    {
        if ($loan->status !== LoanStatus::BORROWED) {
            return false;
        }

        $maxExtensions = (int) setting('max_extensions', 1);
        if ($loan->extensions()->count() >= $maxExtensions) {
            return false;
        }

        $student = $loan->student;
        if ($student && $student->user && ! $student->user->is_active) {
            return false;
        }

        return true;
    }
}
