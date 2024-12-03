<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\BillingController;
use Illuminate\Console\Scheduling\Schedule;
//use LaravelZero\Framework\Commands\Command;


class GenerateBilling extends Command
{
    
    //protected $signature = 'app:generate-billing';
    //protected $description = 'Command description';
    protected $signature = 'billing:generate'; // Command name
    protected $description = 'Generate billing for all users'; // Command description

    
    //maybe handle not needed when ivolk exist
    public function handle()
    {
        // Call the store() method from BillingController
        $controller = new BillingController();
        $controller->storeBySystem(request()); // Pass an empty request if no parameters needed

        $this->info('Billing records generated successfully.');
        return 0;
    }

    public function __invoke()
    {
        $controller = new BillingController();
        $controller->storeBySystem(request()); // Call your store method
        $this->info('Billing generated successfully.');
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->monthly();
    }
    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
