<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SuperAdmin\Financial\Management\EverydayMemberCountAndBillingController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;


class GenerateEverydayManagementBill extends Command
{
    
    protected $signature = 'generate:everyday-management-bill';
    // Command name: php artisan generate:everyday-management-bill
    protected $description = 'Generated Everyday Management bill for all organizations';

    
    public function handle()
    {
        $controller = new EverydayMemberCountAndBillingController();
        $controller->store(request()); // Pass an empty request if no parameters needed

        // Log the command execution and the call to the store function
        Log::info('Command executed and store function called EverydayManagementBilling');

        $this->info('Everyday Management bill generated successfully by System.');
        return 0;
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->daily();
        $schedule->command('generate:everyday-management-bill')->daily()->runInBackground();

    }
    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
