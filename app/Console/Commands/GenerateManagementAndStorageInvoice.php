<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SuperAdmin\Financial\InvoiceController;
use Illuminate\Console\Scheduling\Schedule;

class GenerateManagementAndStorageInvoice extends Command
{
    
    protected $signature = 'generate:management-and-storage-invoice';
    //consol command: php artisan generate:management-and-storage-invoice
    protected $description = 'Generate management and Storage invoice for all organizations';

    
    public function handle()
    {
        $controller = new InvoiceController();
        $controller->managementAndStorageInvoice(request()); // Pass an empty request if no parameters needed

        $this->info('Management and Storage invoice generated successfully by System.');
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
