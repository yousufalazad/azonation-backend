<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BillingController;
use Illuminate\Console\Scheduling\Schedule;

class GenerateBilling extends Command
{
    protected $signature = 'billing:generate'; // Command name
    protected $description = 'Generate billing for all users'; // Command description

    public function handle()
    {
        $controller = new BillingController();
        $controller->storeBySystem(request()); // Pass an empty request if no parameters needed

        $this->info('Billing records generated successfully by System.');
        return 0;
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->daily();
    }
    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
