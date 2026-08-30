<?php

namespace App\Console\Commands;

use App\Services\FineService;
use Illuminate\Console\Command;

class CalculateOverdueFines extends Command
{
    protected $signature = 'fines:calculate-overdue';

    protected $description = 'Create fine records for overdue loans';

    public function handle(): int
    {
        $created = app(FineService::class)->calculateForOverdueLoans();

        $this->info("Created {$created} fine records for overdue loans.");

        return Command::SUCCESS;
    }
}
