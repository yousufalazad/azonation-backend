<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


// Billing. Needs the scheduler to run every minute on the server: * * * * * php artisan schedule:run
// Each organisation's members and storage are counted once a day, near midnight
Schedule::command('generate:everyday-management-bill')->dailyAt('23:50')->withoutOverlapping();
Schedule::command('generate:everyday-storage-bill')->dailyAt('23:55')->withoutOverlapping();
// On the 1st: last month's bills and draft invoices (the Super Admin reviews and publishes them)
Schedule::command('generate:management-and-storage-bill')->monthlyOn(1, '02:00')->withoutOverlapping();
