<?php

namespace App\Console\Commands;

use App\Services\Billing\BillingService;
use App\Models\ManagementAndStorageBilling;

use Illuminate\Console\Command;
use App\Http\Controllers\SuperAdmin\Financial\Management\ManagementAndStorageBillingController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;


class GenerateMonthlyManagementAndStorageBill extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:management-and-storage-bill';
    protected $description = 'Generated management and storage bill for all organizations';

    
    // Last month's bill for every organisation, then a draft invoice for each (nothing is made twice)
    public function handle(BillingService $billing)
    {
        $month = now()->subMonth()->startOfMonth();
        $result = $billing->monthlyBills($month);
        ManagementAndStorageBilling::whereDate('period_start', $month->toDateString())->get()
            ->each(fn ($bill) => $billing->draftInvoice($bill));

        $this->info("Bills for {$result['month']}: {$result['created']} created, {$result['skipped']} skipped. Draft invoices made.");
        return 0;
    }

    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
