<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('loans:mark-overdue')->dailyAt('07:00');
Schedule::command('reservations:expire')->dailyAt('07:05');
Schedule::command('loans:send-reminders')->dailyAt('07:10');
Schedule::command('fines:calculate-overdue')->dailyAt('07:15');
