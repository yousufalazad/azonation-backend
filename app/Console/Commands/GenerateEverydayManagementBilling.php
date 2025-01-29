<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\EverydayMemberCountAndBillingController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;


class GenerateEverydayManagementBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:everyday-management-billing';
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
        $schedule->command('generate:everyday-management-billing')->daily()->runInBackground();

    }
    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
