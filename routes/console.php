<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ Every morning: e-mail borrowers whose items are due today/tomorrow or overdue.
// Keep `php artisan schedule:work` running (or add the cron entry on the server).
\Illuminate\Support\Facades\Schedule::command('returns:send-reminders')->dailyAt('07:00');
