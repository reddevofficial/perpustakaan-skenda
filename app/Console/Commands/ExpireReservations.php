<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'library:expire-reservations';
    protected $description = 'Kadaluarsakan reservasi yang melewati batas pengambilan.';

    public function handle(ReservationService $reservationService): int
    {
        $expired = $reservationService->expire();
        $this->info("{$expired} reservasi kadaluarsa diproses.");

        return self::SUCCESS;
    }
}
