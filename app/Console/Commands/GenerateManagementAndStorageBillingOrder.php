<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Ecommerce\Order\OrderController;
use Illuminate\Console\Scheduling\Schedule;

class GenerateManagementAndStorageBillingOrder extends Command
{
    protected $signature = 'generate:management-and-storage-billing-order'; // Command name
    // Command name: php artisan generate:management-and-storage-billing-order
    protected $description = 'Generate management and storage billing order for all users'; // Command description

    public function handle()
    {
        $controller = new OrderController();
        $controller->generateOrdersFromBillings(request()); // Pass an empty request if no parameters needed

        $this->info('Management and storage billing order records generated successfully by System.');
        return 0;
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->monthly()->runInBackground();
    }
    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
