<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\EverydayStorageBillingController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;


class GenerateEverydayStorageBill extends Command
{
    
    protected $signature = 'generate:everyday-storage-bill';
    protected $description = 'Generated Everyday storage bill for all organizations';

    
    public function handle()
    {
        $controller = new EverydayStorageBillingController();
        $controller->store(request()); // Pass an empty request if no parameters needed

        // Log the command execution and the call to the store function
        Log::info('Command executed and store function called, Everyday storage bill');

        $this->info('Everyday storage bill generated successfully by System.');
        return 0;
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->daily();
        $schedule->command('generate:everyday-storage-billing')->daily()->runInBackground();

    }
    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
