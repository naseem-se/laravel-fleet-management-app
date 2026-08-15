<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fleet:check-maintenance-due')->dailyAt('07:00');
Schedule::command('fleet:check-document-expiry')->dailyAt('07:00');
Schedule::command('fleet:check-license-expiry')->dailyAt('07:00');
Schedule::command('fleet:recalculate-avg-kmpl')->dailyAt('01:00');
Schedule::command('fleet:prune-expired-tokens')->daily();
Schedule::command('fleet:archive-journey-locations')->dailyAt('02:00');