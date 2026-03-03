<?php

use Illuminate\Support\Facades\Schedule;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Instant — setiap jam
Schedule::command('subscription:send-digest instant')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// Daily — setiap hari jam 08:00 WIB (01:00 UTC)
Schedule::command('subscription:send-digest daily')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Weekly terbaru — setiap Senin jam 08:00 WIB
Schedule::command('subscription:send-digest weekly_new')
    ->weeklyOn(1, '01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Weekly terpopuler — setiap Senin jam 09:00 WIB
Schedule::command('subscription:send-digest weekly_popular')
    ->weeklyOn(1, '02:00')
    ->withoutOverlapping()
    ->runInBackground();

// Monthly terpopuler — setiap tanggal 1 jam 08:00 WIB
Schedule::command('subscription:send-digest monthly_popular')
    ->monthlyOn(1, '01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Cleanup OTP setiap 1 jam
Schedule::command('otp:cleanup')->hourly();
