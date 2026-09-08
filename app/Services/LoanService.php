<?php

namespace App\Services;

use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Enums\MemberStatus;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Notifications\LoanStatusNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LoanService
{
    public function request(Student $student, Book $book): Loan
    {
        return DB::transaction(function () use ($student, $book): Loan {
            $student->refresh();
            $book->refresh();

            if ($student->status !== MemberStatus::ACTIVE) {
                throw new RuntimeException('Anggota tidak aktif atau sedang diblokir.');
            }

            $activeLoanCount = $student->loans()
                ->whereIn('status', [LoanStatus::PENDING, LoanStatus::APPROVED, LoanStatus::BORROWED, LoanStatus::OVERDUE])
                ->count();

            if ($activeLoanCount >= SystemSetting::getValue('max_active_loans', 3)) {
                throw new RuntimeException('Batas maksimal peminjaman sudah tercapai.');
            }

            if ($book->status !== 'active' || $book->available_stock < 1) {
                throw new RuntimeException('Buku tidak tersedia untuk dipinjam.');
            }

            $copy = $book->copies()
                ->where('status', BookCopyStatus::AVAILABLE->value)
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw new RuntimeException('Eksemplar buku tidak tersedia.');
            }

            $loan = $student->loans()->create([
                'loan_code' => 'LN-'.strtoupper(Str::random(10)),
                'status' => LoanStatus::PENDING,
                'requested_at' => now(),
            ]);

            $loan->items()->create(['book_copy_id' => $copy->id]);
            $copy->update(['status' => BookCopyStatus::RESERVED]);
            $book->decrement('available_stock');

            $loan = $loan->load(['items.bookCopy.book', 'student.user']);
            $loan->student->user?->notify(new LoanStatusNotification('requested', 'Pengajuan '.$loan->loan_code.' berhasil dikirim.', $loan->id));

            return $loan;
        });
    }

    public function approve(Loan $loan, int $staffId): Loan
    {
        return DB::transaction(function () use ($loan, $staffId): Loan {
            $loan = Loan::query()->with('items.bookCopy')->lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== LoanStatus::PENDING) {
                throw new RuntimeException('Hanya pengajuan menunggu yang dapat disetujui.');
            }

            foreach ($loan->items as $item) {
                if ($item->bookCopy->status !== BookCopyStatus::RESERVED) {
                    throw new RuntimeException('Salah satu eksemplar sudah tidak tersedia.');
                }
            }

            $loan->update([
                'status' => LoanStatus::APPROVED,
                'approved_by' => $staffId,
                'approved_at' => now(),
            ]);

            $loan = $loan->fresh(['items.bookCopy.book', 'student.user']);
            $loan->student->user?->notify(new LoanStatusNotification('approved', 'Peminjaman '.$loan->loan_code.' telah disetujui.', $loan->id));

            return $loan;
        });
    }

    public function reject(Loan $loan, int $staffId, ?string $reason = null): Loan
    {
        return DB::transaction(function () use ($loan, $staffId, $reason): Loan {
            $loan->refresh();

            if ($loan->status !== LoanStatus::PENDING) {
                throw new RuntimeException('Hanya pengajuan menunggu yang dapat ditolak.');
            }

            $loan->update([
                'status' => LoanStatus::REJECTED,
                'approved_by' => $staffId,
                'approved_at' => now(),
                'notes' => $reason,
            ]);

            foreach ($loan->items()->with('bookCopy')->get() as $item) {
                if ($item->bookCopy?->status === BookCopyStatus::RESERVED) {
                    $item->bookCopy->update(['status' => BookCopyStatus::AVAILABLE]);
                    $item->bookCopy->book()->increment('available_stock');
                }
            }

            $loan = $loan->fresh(['student.user']);
            $loan->student->user?->notify(new LoanStatusNotification('rejected', 'Peminjaman '.$loan->loan_code.' ditolak.', $loan->id));

            return $loan;
        });
    }

    public function markBorrowed(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan): Loan {
            $loan = Loan::query()->with('items.bookCopy.book')->lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== LoanStatus::APPROVED) {
                throw new RuntimeException('Peminjaman belum disetujui.');
            }

            $duration = (int) SystemSetting::getValue('loan_duration_days', 7);

            foreach ($loan->items as $item) {
                $copy = $item->bookCopy->fresh();
                if (! $copy || $copy->status !== BookCopyStatus::RESERVED) {
                    throw new RuntimeException('Eksemplar tidak tersedia saat diserahkan.');
                }

                $copy->update(['status' => BookCopyStatus::BORROWED]);
            }

            $loan->update([
                'status' => LoanStatus::BORROWED,
                'borrowed_at' => now(),
                'due_at' => now()->addDays($duration)->toDateString(),
            ]);

            return $loan->fresh(['items.bookCopy.book', 'student.user']);
        });
    }
}
