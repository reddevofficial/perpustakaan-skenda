<?php

namespace App\Console\Commands;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendLoanReminders extends Command
{
    protected $signature = 'loans:send-reminders';

    protected $description = 'Send reminders for loans approaching due date or overdue';

    public function handle(): int
    {
        $notificationService = app(NotificationService::class);
        $sent = 0;

        $activeLoans = Loan::with(['student.user', 'items.bookCopy.book'])
            ->where('status', LoanStatus::BORROWED)
            ->whereNotNull('due_at')
            ->where('due_at', '>', now())
            ->get();

        foreach ($activeLoans as $loan) {
            $daysUntilDue = (int) now()->diffInDays($loan->due_at, false);

            if ($daysUntilDue <= 3 && $daysUntilDue > 1) {
                $this->sendIfNotAlready($loan, 'loan_reminder_h3', $notificationService);
                $sent++;
            } elseif ($daysUntilDue <= 1 && $daysUntilDue > 0) {
                $this->sendIfNotAlready($loan, 'loan_reminder_h1', $notificationService);
                $sent++;
            } elseif ($daysUntilDue === 0) {
                $this->sendIfNotAlready($loan, 'loan_reminder_due', $notificationService);
                $sent++;
            }
        }

        $overdueLoans = Loan::with(['student.user', 'items.bookCopy.book'])
            ->where('status', LoanStatus::BORROWED)
            ->where('due_at', '<', now())
            ->get();

        foreach ($overdueLoans as $loan) {
            $this->sendIfNotAlready($loan, 'loan_overdue', $notificationService);
            $sent++;
        }

        $this->info("Sent {$sent} reminders.");

        return Command::SUCCESS;
    }

    protected function sendIfNotAlready(Loan $loan, string $type, NotificationService $service): void
    {
        if (! $loan->student?->user) {
            return;
        }

        $alreadySent = $loan->student->user->notifications()
            ->where('data->type', $type)
            ->whereDate('created_at', today())
            ->exists();

        if (! $alreadySent) {
            $service->sendLoanStatusUpdate($loan, $type);
        }
    }
}
