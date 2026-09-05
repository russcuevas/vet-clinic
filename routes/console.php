<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Schedule Annual Leave Archive & Permanent Snapshot
 * Runs every January 1 at 1:00 AM (01:00) to archive the concluding year's leave records (5 VL, 5 SL, 2 SPL & SL Monetization) for file keeping.
 */
Schedule::command('leaves:annual-archive')->yearlyOn(1, 1, '01:00');
