<?php

namespace App\Services;

use App\Enums\FineStatus;
use App\Enums\LoanStatus;
use App\Models\AuditLog;
use App\Models\Fine;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;

class FineService
{
    public function calculateForLoan(Loan $loan): ?Fine
    {
        if (! $loan->returned_at || ! $loan->due_at) {
            return null;
        }

        if ($loan->returned_at->lte($loan->due_at)) {
            return null;
        }

        $existingFine = Fine::where('loan_id', $loan->id)->first();
        if ($existingFine) {
            return $existingFine;
        }

        $lateDays = (int) $loan->due_at->diffInDays($loan->returned_at);
        $gracePeriod = (int) setting('grace_period_days', 0);
        $effectiveDays = max(0, $lateDays - $gracePeriod);

        if ($effectiveDays <= 0) {
            return null;
        }

        $finePerDay = (float) setting('fine_per_day', 1000);
        $maxFine = setting('max_fine');
        $amount = $effectiveDays * $finePerDay;

        if ($maxFine !== null && (float) $maxFine > 0) {
            $amount = min($amount, (float) $maxFine);
        }

        $fine = Fine::create([
            'loan_id' => $loan->id,
            'murid_id' => $loan->murid_id,
            'amount' => $amount,
            'late_days' => $effectiveDays,
            'status' => FineStatus::UNPAID,
            'notes' => "Denda {$effectiveDays} hari keterlambatan (Rp{$finePerDay}/hari)",
        ]);

        AuditLog::log('fine_created', $fine, null, $fine->toArray());

        return $fine;
    }

    public function calculateForOverdueLoans(): int
    {
        $overdueLoans = Loan::where('status', LoanStatus::BORROWED)
            ->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereDoesntHave('fines')
            ->get();

        $created = 0;
        foreach ($overdueLoans as $loan) {
            $lateDays = (int) $loan->due_at->diffInDays(now());
            $gracePeriod = (int) setting('grace_period_days', 0);
            $effectiveDays = max(0, $lateDays - $gracePeriod);

            if ($effectiveDays <= 0) {
                continue;
            }

            $finePerDay = (float) setting('fine_per_day', 1000);
            $maxFine = setting('max_fine');
            $amount = $effectiveDays * $finePerDay;

            if ($maxFine !== null && (float) $maxFine > 0) {
                $amount = min($amount, (float) $maxFine);
            }

            Fine::create([
                'loan_id' => $loan->id,
                'murid_id' => $loan->murid_id,
                'amount' => $amount,
                'late_days' => $effectiveDays,
                'status' => FineStatus::UNPAID,
                'notes' => "Denda {$effectiveDays} hari keterlambatan (Rp{$finePerDay}/hari)",
            ]);

            $created++;
        }

        return $created;
    }

    public function pay(Fine $fine, int $paidBy, ?string $notes = null): void
    {
        DB::transaction(function () use ($fine, $paidBy, $notes) {
            $fine->update([
                'status' => FineStatus::PAID,
                'paid_at' => now(),
                'paid_by' => $paidBy,
                'notes' => $notes ?? $fine->notes,
            ]);

            AuditLog::log('fine_paid', $fine, ['status' => FineStatus::UNPAID->value], ['status' => FineStatus::PAID->value]);
        });
    }

    public function waive(Fine $fine, int $waivedBy, string $reason): void
    {
        DB::transaction(function () use ($fine, $waivedBy, $reason) {
            $fine->update([
                'status' => FineStatus::WAIVED,
                'paid_by' => $waivedBy,
                'notes' => $reason,
            ]);

            AuditLog::log('fine_waived', $fine, ['status' => FineStatus::UNPAID->value], ['status' => FineStatus::WAIVED->value]);
        });
    }

    public function getUnpaidTotal(int $muridId): float
    {
        return (float) Fine::where('murid_id', $muridId)
            ->where('status', FineStatus::UNPAID)
            ->sum('amount');
    }

    public function hasUnpaidFines(int $muridId): bool
    {
        return Fine::where('murid_id', $muridId)
            ->where('status', FineStatus::UNPAID)
            ->exists();
    }
}
