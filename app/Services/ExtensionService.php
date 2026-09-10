<?php

namespace App\Services;

use App\Enums\ExtensionStatus;
use App\Enums\LoanStatus;
use App\Models\Extension;
use App\Models\Loan;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Notifications\LoanStatusNotification;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExtensionService
{
    public function request(Student $student, Loan $loan, ?string $reason = null): Extension
    {
        if ($loan->student_id !== $student->id || $loan->status !== LoanStatus::BORROWED) {
            throw new RuntimeException('Peminjaman tidak dapat diperpanjang.');
        }

        if (! $loan->due_at || $loan->due_at->isPast()) {
            throw new RuntimeException('Peminjaman sudah terlambat.');
        }

        $maximum = (int) SystemSetting::getValue('max_extensions', 1);
        if ($loan->extension_count >= $maximum || $loan->extensions()->where('status', ExtensionStatus::PENDING)->exists()) {
            throw new RuntimeException('Batas perpanjangan sudah tercapai atau masih menunggu persetujuan.');
        }

        return $loan->extensions()->create([
            'student_id' => $student->id,
            'status' => ExtensionStatus::PENDING,
            'extension_number' => $loan->extension_count + 1,
            'requested_due_at' => $loan->due_at,
            'reason' => $reason,
        ]);
    }

    public function approve(Extension $extension, int $staffId): Extension
    {
        return DB::transaction(function () use ($extension, $staffId): Extension {
            $extension = Extension::query()->with('loan')->lockForUpdate()->findOrFail($extension->id);
            $loan = $extension->loan;

            if ($extension->status !== ExtensionStatus::PENDING || $loan->status !== LoanStatus::BORROWED) {
                throw new RuntimeException('Permintaan perpanjangan tidak valid.');
            }

            $days = (int) SystemSetting::getValue('extension_duration_days', 7);
            $newDueDate = $loan->due_at->copy()->addDays($days);
            $loan->update(['due_at' => $newDueDate, 'extension_count' => $loan->extension_count + 1]);
            $extension->update([
                'status' => ExtensionStatus::APPROVED,
                'approved_by' => $staffId,
                'approved_due_at' => $newDueDate,
            ]);

            $extension = $extension->fresh(['loan', 'student.user']);
            $extension->student->user?->notify(new LoanStatusNotification('extension_approved', 'Perpanjangan peminjaman disetujui.', $loan->id));

            return $extension;
        });
    }

    public function reject(Extension $extension, int $staffId, ?string $reason = null): Extension
    {
        if ($extension->status !== ExtensionStatus::PENDING) {
            throw new RuntimeException('Permintaan perpanjangan sudah diproses.');
        }

        $extension->update([
            'status' => ExtensionStatus::REJECTED,
            'approved_by' => $staffId,
            'reason' => $reason ?: $extension->reason,
        ]);

        $extension = $extension->fresh(['loan', 'student.user']);
        $extension->student->user?->notify(new LoanStatusNotification('extension_rejected', 'Perpanjangan peminjaman ditolak.', $extension->loan_id));

        return $extension;
    }
}
