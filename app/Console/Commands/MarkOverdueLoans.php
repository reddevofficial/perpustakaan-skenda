<?php

namespace App\Console\Commands;

use App\Enums\LoanItemStatus;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Console\Command;

class MarkOverdueLoans extends Command
{
    protected $signature = 'loans:mark-overdue';

    protected $description = 'Mark loans as overdue when due_at has passed';

    public function handle(): int
    {
        $overdueLoans = Loan::where('status', LoanStatus::BORROWED)
            ->where('due_at', '<', now())
            ->get();

        $count = 0;
        foreach ($overdueLoans as $loan) {
            $loan->update(['status' => LoanStatus::OVERDUE]);
            $loan->items()
                ->where('status', LoanItemStatus::BORROWED)
                ->update(['status' => LoanItemStatus::OVERDUE]);
            $count++;
        }

        $this->info("Marked {$count} loans as overdue.");

        return Command::SUCCESS;
    }
}
