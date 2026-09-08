<?php

namespace App\Console\Commands;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Notifications\LoanStatusNotification;
use App\Services\FineService;
use Illuminate\Console\Command;

class CheckOverdueLoans extends Command
{
    protected $signature = 'library:check-overdue';
    protected $description = 'Tandai peminjaman terlambat dan sinkronkan denda.';

    public function handle(FineService $fineService): int
    {
        Loan::query()
            ->with('student.user')
            ->whereIn('status', [LoanStatus::BORROWED->value, LoanStatus::OVERDUE->value])
            ->whereDate('due_at', '<', today())
            ->chunkById(100, function ($loans) use ($fineService): void {
                foreach ($loans as $loan) {
                    if ($loan->status !== LoanStatus::OVERDUE) {
                        $loan->update(['status' => LoanStatus::OVERDUE]);
                    }

                    $fine = $fineService->sync($loan);
                    $loan->student?->user?->notify(new LoanStatusNotification(
                        'overdue',
                        'Peminjaman '.$loan->loan_code.' telah melewati tanggal jatuh tempo.',
                        $loan->id,
                    ));

                    $this->line(sprintf('%s: %d hari, denda Rp%d', $loan->loan_code, $fine?->overdue_days ?? 0, $fine?->total_amount ?? 0));
                }
            });

        return self::SUCCESS;
    }
}
