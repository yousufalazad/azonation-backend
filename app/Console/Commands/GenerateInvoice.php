<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\InvoiceController;
use Illuminate\Console\Scheduling\Schedule;

class GenerateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:invoice';
    protected $description = 'Generate invoice for all organizations';

    
    public function handle()
    {
        $controller = new InvoiceController();
        $controller->storeBySystem(request()); // Pass an empty request if no parameters needed

        $this->info('Invoice generated successfully by System.');
        return 0;
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
