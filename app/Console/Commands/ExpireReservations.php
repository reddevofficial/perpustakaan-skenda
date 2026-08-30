<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Expire reservations that were not picked up in time';

    public function handle(): int
    {
        app(ReservationService::class)->expireReservations();

        $this->info('Expired reservations processed.');

        return Command::SUCCESS;
    }
}
