<?php

use App\Modules\Core\Support\ModuleGate;
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

// guest carts nobody attached to an account are cleared after 30 days.
// A disabled module never registers its commands, and the scheduler would then
// fail on this line every night, so the entry goes off with the module.
if (ModuleGate::isEnabled('shop_cart')) {
    Schedule::command('cart:clear-abandoned')->daily();
}

// Shared hosting has no Supervisor to keep `queue:work` alive, so the cron that runs
// schedule:run also drains the queue (letters, SMS) once a minute and exits.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')
    ->everyMinute()
    ->withoutOverlapping();
