<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('hr:sync-cashiers')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('cashier-variances:process-outbox')
    ->everyFiveMinutes()
    ->withoutOverlapping();
