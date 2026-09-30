<?php

namespace App\Services\Billing;

use App\Models\EverydayMemberCountAndBilling;
use App\Models\Invoice;
use App\Models\ManagementAndStorageBilling;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Storage\EverydayStorageBillingProxy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turning each organisation's daily usage into money:
 *   1. monthlyBills(): one bill per organisation per month, summed from the daily records
 *   2. draftInvoice(): an invoice (unpublished) built from a bill, with the region's tax
 *   3. publish(): the organisation can now see and pay it
 *   4. recordPayment(): a payment and receipt, and the invoice's paid and due amounts
 * Each step can be run again safely: nothing is created twice.
 */
class BillingService
{
    public const PAYMENT_DAYS = 14;

    // ---- 1. Monthly bills ----

    /** @return array{created:int, skipped:int, month:string} */
    public function monthlyBills(Carbon $month): array
    {
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();
        $created = 0;
        $skipped = 0;

        User::where('type', 'organisation')->whereNull('deleted_at')->orderBy('id')
            ->each(function (User $org) use ($start, $end, &$created, &$skipped) {
                $exists = ManagementAndStorageBilling::where('user_id', $org->id)
                    ->whereDate('period_start', $start->toDateString())->exists();
                if ($exists) {
                    $skipped++;
                    return;
                }

                $days = EverydayMemberCountAndBilling::where('user_id', $org->id)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
                $memberDays = (int) (clone $days)->sum('day_total_member');
                $management = (float) (clone $days)->sum('day_total_bill');
                $storage = (float) DB::table('everyday_storage_billings')->where('user_id', $org->id)
                    ->whereBetween('date', [$start->toDateString(), $end->toDateString()])->sum('day_bill_amount');

                // Nothing used that month: no bill
                if ($memberDays === 0 && $management == 0 && $storage == 0) {
                    $skipped++;
                    return;
                }

                ManagementAndStorageBilling::create([
                    'user_id' => $org->id,
                    'org_name' => $org->org_name,
                    'service_month' => $start->format('F'),
                    'service_year' => $start->format('Y'),
                    'billing_month' => $end->copy()->addDay()->format('F'),
                    'billing_year' => $end->copy()->addDay()->format('Y'),
                    'period_start' => $start->toDateString(),
                    'period_end' => $end->toDateString(),
                    'total_member' => $memberDays,
                    'total_management_bill_amount' => round($management, 2),
                    'total_storage_bill_amount' => round($storage, 2),
                    'currency_code' => $this->currencyFor($org->id) ?? 'BDT',
                    'bill_status' => 'issued',
                    'is_active' => 1,
                ]);
                $created++;
            });

        return ['created' => $created, 'skipped' => $skipped, 'month' => $start->format('Y-m')];
    }

    // ---- 2. Draft invoice from a bill ----

    public function draftInvoice(ManagementAndStorageBilling $bill): Invoice
    {
        $existing = Invoice::where('billing_code', $bill->billing_code)->first();
        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($bill) {
            $org = User::find($bill->user_id);
            $region = $this->regionFor($bill->user_id);
            $taxRate = $region ? (float) DB::table('regional_tax_rates')->where('region_id', $region->id)->where('is_active', 1)->value('tax_rate') : 0;
            $subTotal = round((float) $bill->total_management_bill_amount + (float) $bill->total_storage_bill_amount, 2);
            $tax = round($subTotal * $taxRate / 100, 2);
            $total = $subTotal + $tax;
            $period = "{$bill->service_month} {$bill->service_year}";

            $order = Order::create([
                'user_id' => $bill->user_id,
                'billing_code' => $bill->billing_code,
                'order_date' => now(),
                'user_name' => $org?->org_name,
                'sub_total' => $subTotal,
                'discount_amount' => 0,
                'shipping_cost' => 0,
                'total_tax' => $tax,
                'credit_applied' => 0,
                'total_amount' => $total,
                'tax_rate' => $taxRate,
                'currency_code' => $bill->currency_code,
                'payment_method' => 'Bank transfer',
                'billing_address' => $this->addressFor($bill->user_id) ?? '',
                'user_country' => $this->countryNameFor($bill->user_id) ?? '',
                'user_region' => $region?->name ?? '',
                'is_active' => 1,
            ]);
            foreach ([
                ["Organisation management, $period ({$bill->total_member} member-days)", $bill->total_management_bill_amount],
                ["File storage, $period", $bill->total_storage_bill_amount],
            ] as [$name, $amount]) {
                if ((float) $amount <= 0) {
                    continue;
                }
                OrderItem::create([
                    'order_id' => $order->id, 'product_id' => null, 'product_name' => $name,
                    'unit_price' => $amount, 'quantity' => 1, 'total_price' => $amount, 'discount_amount' => 0, 'is_active' => 1,
                ]);
            }

            return Invoice::create([
                'billing_code' => $bill->billing_code,
                'order_code' => $order->order_code,
                'order_id' => $order->id,
                'user_id' => $bill->user_id,
                'org_name' => $org?->org_name ?? $bill->org_name,
                'description' => "Azonation for $period",
                'total_amount' => $total,
                'amount_paid' => 0,
                'balance_due' => $total,
                'currency_code' => $bill->currency_code,
                'generate_date' => now()->toDateString(),
                'is_published' => 0,
                'invoice_status' => 'draft',
                'payment_status' => 'unpaid',
                'is_active' => 1,
            ]);
        });
    }

    // ---- 3. Publish ----

    public function publish(Invoice $invoice): Invoice
    {
        if (!$invoice->is_published) {
            $invoice->update([
                'is_published' => 1,
                'invoice_status' => 'issued',
                'issue_date' => now()->toDateString(),
                'due_date' => $invoice->due_date ?: now()->addDays(self::PAYMENT_DAYS)->toDateString(),
            ]);
        }
        return $invoice;
    }

    // ---- 4. Payment ----

    /** @param array{amount:float, method:string, paid_on:string, reference?:?string, note?:?string} $data */
    public function recordPayment(Invoice $invoice, array $data, ?User $admin = null): array
    {
        return DB::transaction(function () use ($invoice, $data, $admin) {
            $invoice = Invoice::lockForUpdate()->findOrFail($invoice->id);
            $org = User::find($invoice->user_id);
            $amount = round((float) $data['amount'], 2);

            $paymentId = DB::table('payments')->insertGetId([
                'azon_payment_txn_id' => 'P' . strtoupper(Str::random(14)),
                'invoice_id' => $invoice->id,
                'user_id' => $invoice->user_id,
                'user_name' => $org?->org_name,
                'user_email' => $org?->email,
                'gateway_reference_id' => $data['reference'] ?? null,
                'payment_method' => $data['method'],
                'source_type' => 'admin',
                'transaction_status' => 'successful',
                'amount' => $amount,
                'currency' => $invoice->currency_code,
                'payment_time' => Carbon::parse($data['paid_on'])->startOfDay(),
                'payment_note' => $data['note'] ?? null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $receiptId = DB::table('receipts')->insertGetId([
                'payment_id' => $paymentId,
                'user_id' => $invoice->user_id,
                'receipt_code' => 'R' . strtoupper(Str::random(12)),
                'invoice_id' => $invoice->id,
                'amount_received' => $amount,
                'currency_code' => $invoice->currency_code,
                'gateway_type' => $data['method'],
                'transaction_reference' => $data['reference'] ?? null,
                'payment_date' => Carbon::parse($data['paid_on'])->toDateString(),
                'note' => $data['note'] ?? null,
                'status' => 'processed',
                'admin_note' => $admin ? "Recorded by {$admin->email}" : null,
                'is_published' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $paid = round((float) $invoice->amount_paid + $amount, 2);
            $due = max(0, round((float) $invoice->total_amount - $paid, 2));
            $invoice->update([
                'amount_paid' => $paid,
                'balance_due' => $due,
                'payment_status' => $due <= 0 ? 'paid' : 'unpaid',
            ]);

            return ['payment_id' => $paymentId, 'receipt_id' => $receiptId, 'invoice' => $invoice->fresh()];
        });
    }

    // ---- Where the organisation is ----

    private function regionFor(int $userId): ?object
    {
        return DB::table('user_countries as uc')
            ->join('country_regions as cr', 'cr.country_id', '=', 'uc.country_id')
            ->join('regions as r', 'r.id', '=', 'cr.region_id')
            ->where('uc.user_id', $userId)
            ->first(['r.id', 'r.name']);
    }

    private function currencyFor(int $userId): ?string
    {
        $region = $this->regionFor($userId);
        if (!$region) {
            return null;
        }
        return DB::table('region_currencies as rc')->join('currencies as c', 'c.id', '=', 'rc.currency_id')
            ->where('rc.region_id', $region->id)->value('c.currency_code');
    }

    private function countryNameFor(int $userId): ?string
    {
        return DB::table('user_countries as uc')->join('countries as c', 'c.id', '=', 'uc.country_id')
            ->where('uc.user_id', $userId)->value('c.name');
    }

    private function addressFor(int $userId): ?string
    {
        $a = DB::table('addresses')->where('user_id', $userId)->first();
        if (!$a) {
            return null;
        }
        return collect([$a->address_line_one ?? null, $a->address_line_two ?? null, $a->city ?? null, $a->state_or_region ?? null, $a->postcode ?? null])
            ->filter()->implode(', ');
    }
}
