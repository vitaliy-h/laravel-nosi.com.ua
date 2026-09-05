<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * The whole schedule lives here, not in bootstrap/app.php: withSchedule() registers
 * through Artisan::starting(), so a schedule declared there is invisible until the
 * console kernel boots — including in tests.
 */

// journals keep records for config('log.retention_days') days (one year by default)
Schedule::command('logs:prune')->dailyAt('03:00');

// guest carts nobody attached to an account are cleared after 30 days
Schedule::command('cart:clear-abandoned')->daily();
