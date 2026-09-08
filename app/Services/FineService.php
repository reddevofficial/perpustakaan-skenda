<?php

namespace App\Services;

use App\Enums\FineStatus;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\SystemSetting;
use Illuminate\Support\Carbon;

class FineService
{
    public function sync(Loan $loan): ?Fine
    {
        if (! $loan->due_at || ! $loan->borrowed_at) {
            return null;
        }

        $endDate = $loan->returned_at?->toDateString() ?? now()->toDateString();
        $overdueDays = max(0, Carbon::parse($loan->due_at)->diffInDays(Carbon::parse($endDate), false));
        $dailyRate = (int) SystemSetting::getValue('fine_daily_rate', 1000);

        if ($overdueDays === 0) {
            return $loan->fine;
        }

        return $loan->fine()->updateOrCreate(
            ['loan_id' => $loan->id],
            [
                'student_id' => $loan->student_id,
                'overdue_days' => $overdueDays,
                'daily_rate' => $dailyRate,
                'total_amount' => $overdueDays * $dailyRate,
                'status' => $loan->fine?->status ?? FineStatus::UNPAID,
            ],
        );
    }
}
