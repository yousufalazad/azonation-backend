<?php

namespace App\Console\Commands;

use App\Services\Billing\BillingService;
use App\Models\ManagementAndStorageBilling;

use Illuminate\Console\Command;
use App\Http\Controllers\Ecommerce\Order\OrderController;
use Illuminate\Console\Scheduling\Schedule;

class GenerateManagementAndStorageBillingOrder extends Command
{
    protected $signature = 'generate:management-and-storage-billing-order'; // Command name
    // Command name: php artisan generate:management-and-storage-billing-order
    protected $description = 'Generate management and storage billing order for all users'; // Command description

    // Draft invoices (with their order lines) for last month's bills; generate:management-and-storage-bill does this too
    public function handle(BillingService $billing)
    {
        ManagementAndStorageBilling::whereDate('period_start', now()->subMonth()->startOfMonth()->toDateString())->get()
            ->each(fn ($bill) => $billing->draftInvoice($bill));

        $this->info('Draft invoices made for the bills of last month.');
        return 0;
    }

    // You can adjust the frequency depending on your needs:
	// •	->daily() — Runs daily.
	// •	->weekly() — Runs weekly.
	// •	->monthly() — Runs monthly.
	// •	->cron('0 0 1 * *') — Custom cron expression.
}
