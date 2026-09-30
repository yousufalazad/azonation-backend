<?php

namespace App\Http\Controllers\SuperAdmin\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\ManagementAndStorageBilling;
use App\Models\ManagementPackage;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Super Admin billing: monthly bills, invoices and payments, plans and their prices,
 * and which plan each organisation is on. Super Admin only (see the guard in routes/api.php).
 */
class BillingAdminController extends Controller
{
    public const METHODS = ['bank_transfer', 'cash', 'cheque', 'card', 'bkash', 'rocket', 'sslcommerze', 'stripe', 'paypal', 'other'];

    public function __construct(private BillingService $billing) {}

    // ---- Monthly bills ----

    public function bills(Request $request)
    {
        $month = $request->filled('month') ? Carbon::parse($request->month . '-01') : null;
        $bills = ManagementAndStorageBilling::query()
            ->when($month, fn ($q) => $q->whereDate('period_start', $month->toDateString()))
            ->orderByDesc('period_start')->orderBy('org_name')
            ->limit(1000)->get();
        $invoices = Invoice::whereIn('billing_code', $bills->pluck('billing_code')->filter())
            ->get(['id', 'billing_code', 'invoice_code', 'is_published', 'payment_status', 'balance_due'])->keyBy('billing_code');

        return response()->json(['status' => true, 'data' => $bills->map(fn ($b) => $b->toArray() + [
            'total' => round((float) $b->total_management_bill_amount + (float) $b->total_storage_bill_amount, 2),
            'invoice' => $invoices[$b->billing_code] ?? null,
        ])->values()]);
    }

    public function generateBills(Request $request)
    {
        $data = $request->validate(['month' => 'required|date_format:Y-m|before:' . now()->startOfMonth()->toDateString()]);
        $result = $this->billing->monthlyBills(Carbon::parse($data['month'] . '-01'));

        return response()->json(['status' => true, 'data' => $result]);
    }

    public function invoiceBill($id)
    {
        $invoice = $this->billing->draftInvoice(ManagementAndStorageBilling::findOrFail($id));

        return response()->json(['status' => true, 'data' => $invoice]);
    }

    // Draft invoices for every bill of the month that has none yet
    public function invoiceMonth(Request $request)
    {
        $data = $request->validate(['month' => 'required|date_format:Y-m']);
        $bills = ManagementAndStorageBilling::whereDate('period_start', Carbon::parse($data['month'] . '-01')->toDateString())->get();
        $had = Invoice::whereIn('billing_code', $bills->pluck('billing_code'))->count();
        $bills->each(fn ($b) => $this->billing->draftInvoice($b));

        return response()->json(['status' => true, 'data' => ['created' => $bills->count() - $had, 'already' => $had]]);
    }

    // ---- Invoices ----

    public function invoices(Request $request)
    {
        $status = $request->query('status'); // draft | unpaid | overdue | paid | cancelled
        $today = Carbon::today()->toDateString();
        $invoices = Invoice::query()
            ->when($status === 'draft', fn ($q) => $q->where('is_published', 0)->where('invoice_status', '!=', 'cancelled'))
            ->when($status === 'unpaid', fn ($q) => $q->where('is_published', 1)->where('balance_due', '>', 0)->whereNotIn('payment_status', ['paid', 'cancelled', 'refunded']))
            ->when($status === 'overdue', fn ($q) => $q->where('is_published', 1)->where('balance_due', '>', 0)->whereNotIn('payment_status', ['paid', 'cancelled', 'refunded'])->whereDate('due_date', '<', $today))
            ->when($status === 'paid', fn ($q) => $q->where('payment_status', 'paid'))
            ->when($status === 'cancelled', fn ($q) => $q->where('invoice_status', 'cancelled'))
            ->orderByDesc('id')->limit(1000)
            ->get(['id', 'invoice_code', 'billing_code', 'user_id', 'org_name', 'description', 'total_amount', 'amount_paid', 'balance_due',
                'currency_code', 'issue_date', 'due_date', 'is_published', 'invoice_status', 'payment_status', 'created_at']);

        return response()->json(['status' => true, 'data' => $invoices]);
    }

    public function invoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        $items = DB::table('order_items')->where('order_id', $invoice->order_id)->get(['product_name', 'unit_price', 'quantity', 'total_price']);
        $order = $invoice->order_id ? DB::table('orders')->where('id', $invoice->order_id)->first(['sub_total', 'total_tax', 'tax_rate', 'discount_amount', 'billing_address', 'user_country']) : null;
        $receipts = DB::table('receipts')->where('invoice_id', $invoice->id)->orderByDesc('payment_date')
            ->get(['id', 'receipt_code', 'amount_received', 'currency_code', 'gateway_type', 'transaction_reference', 'payment_date', 'note', 'status', 'admin_note']);
        $org = DB::table('users')->where('id', $invoice->user_id)->first(['id', 'org_name', 'email']);

        return response()->json(['status' => true, 'data' => [
            'invoice' => $invoice, 'items' => $items, 'order' => $order, 'receipts' => $receipts, 'organisation' => $org,
        ]]);
    }

    public function updateInvoice(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        $data = $request->validate([
            'due_date' => 'nullable|date',
            'invoice_note' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:1000',
            'admin_note' => 'nullable|string|max:255',
        ]);
        $invoice->update($data);

        return response()->json(['status' => true, 'data' => $invoice]);
    }

    public function publishInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);
        abort_if($invoice->invoice_status === 'cancelled', 422, 'A cancelled invoice cannot be published.');

        return response()->json(['status' => true, 'data' => $this->billing->publish($invoice)]);
    }

    // Publish every draft (optionally for one month's bills)
    public function publishDrafts(Request $request)
    {
        $drafts = Invoice::where('is_published', 0)->where('invoice_status', '!=', 'cancelled')->get();
        $drafts->each(fn ($i) => $this->billing->publish($i));

        return response()->json(['status' => true, 'data' => ['published' => $drafts->count()]]);
    }

    public function cancelInvoice(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        abort_if((float) $invoice->amount_paid > 0, 422, 'This invoice has payments. Refund them before cancelling.');
        $invoice->update(['invoice_status' => 'cancelled', 'payment_status' => 'cancelled', 'admin_note' => $request->input('reason') ?: $invoice->admin_note]);

        return response()->json(['status' => true, 'data' => $invoice]);
    }

    public function recordPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        abort_if($invoice->invoice_status === 'cancelled', 422, 'This invoice is cancelled.');
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . max(0.01, (float) $invoice->balance_due),
            'method' => ['required', Rule::in(self::METHODS)],
            'paid_on' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:255',
        ]);

        return response()->json(['status' => true, 'data' => $this->billing->recordPayment($invoice, $data, $request->user())]);
    }

    public function payments(Request $request)
    {
        $rows = DB::table('receipts as r')
            ->leftJoin('invoices as i', 'i.id', '=', 'r.invoice_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->orderByDesc('r.payment_date')->orderByDesc('r.id')->limit(1000)
            ->get(['r.id', 'r.receipt_code', 'r.amount_received', 'r.currency_code', 'r.gateway_type', 'r.transaction_reference', 'r.payment_date',
                'r.status', 'r.note', 'r.invoice_id', 'i.invoice_code', 'u.org_name']);

        return response()->json(['status' => true, 'data' => $rows]);
    }

    // ---- Daily usage (what the bills are made from) ----

    // One month: per organisation, the days counted, member-days and amounts (?org_id= for each day)
    public function daily(Request $request)
    {
        $data = $request->validate(['month' => 'required|date_format:Y-m', 'org_id' => 'nullable|integer']);
        $start = Carbon::parse($data['month'] . '-01')->startOfMonth()->toDateString();
        $end = Carbon::parse($data['month'] . '-01')->endOfMonth()->toDateString();

        if (!empty($data['org_id'])) {
            $members = DB::table('everyday_member_count_and_billings')->where('user_id', $data['org_id'])
                ->whereBetween('date', [$start, $end])->pluck('day_total_member', 'date');
            $bills = DB::table('everyday_member_count_and_billings')->where('user_id', $data['org_id'])
                ->whereBetween('date', [$start, $end])->pluck('day_total_bill', 'date');
            $storage = DB::table('everyday_storage_billings')->where('user_id', $data['org_id'])
                ->whereBetween('date', [$start, $end])->pluck('day_bill_amount', 'date');
            $days = collect($members->keys())->merge($storage->keys())->unique()->sort()->values()
                ->map(fn ($d) => ['date' => $d, 'members' => (int) ($members[$d] ?? 0), 'management' => (float) ($bills[$d] ?? 0), 'storage' => (float) ($storage[$d] ?? 0)]);
            return response()->json(['status' => true, 'data' => ['days' => $days]]);
        }

        $management = DB::table('everyday_member_count_and_billings')->whereBetween('date', [$start, $end])
            ->select('user_id', DB::raw('count(*) as days'), DB::raw('sum(day_total_member) as member_days'), DB::raw('sum(day_total_bill) as amount'))
            ->groupBy('user_id')->get()->keyBy('user_id');
        $storage = DB::table('everyday_storage_billings')->whereBetween('date', [$start, $end])
            ->select('user_id', DB::raw('sum(day_bill_amount) as amount'))->groupBy('user_id')->pluck('amount', 'user_id');
        $orgIds = $management->keys()->merge($storage->keys())->unique();
        $names = DB::table('users')->whereIn('id', $orgIds)->pluck('org_name', 'id');

        return response()->json(['status' => true, 'data' => ['organisations' => $orgIds->map(fn ($id) => [
            'org_id' => (int) $id,
            'org_name' => $names[$id] ?? null,
            'days' => (int) ($management[$id]->days ?? 0),
            'member_days' => (int) ($management[$id]->member_days ?? 0),
            'management' => round((float) ($management[$id]->amount ?? 0), 2),
            'storage' => round((float) ($storage[$id] ?? 0), 2),
        ])->sortBy('org_name')->values()]]);
    }

    // ---- Plans and prices ----

    public function plans()
    {
        $plans = ManagementPackage::orderBy('id')->get();
        $prices = DB::table('management_pricings as p')->leftJoin('regions as r', 'r.id', '=', 'p.region_id')
            ->get(['p.id', 'p.region_id', 'p.management_package_id', 'p.price_rate', 'p.is_active', 'r.name as region_name'])
            ->groupBy('management_package_id');
        $counts = DB::table('management_subscriptions')->where('is_active', 1)
            ->select('management_package_id', DB::raw('count(*) as n'))->groupBy('management_package_id')->pluck('n', 'management_package_id');
        $regions = DB::table('regions')->where('is_active', 1)->get(['id', 'name', 'title']);

        return response()->json(['status' => true, 'data' => [
            'plans' => $plans->map(fn ($p) => $p->toArray() + [
                'prices' => ($prices[$p->id] ?? collect())->values(),
                'organisations' => (int) ($counts[$p->id] ?? 0),
            ]),
            'regions' => $regions,
        ]]);
    }

    public function updatePlan(Request $request, $id)
    {
        $plan = ManagementPackage::findOrFail($id);
        $limits = ['max_member', 'storage_limit', 'meeting_limit', 'event_limit', 'project_limit', 'asset_limit', 'document_limit'];
        $features = ['report', 'advanced_report', 'custom_report', 'custom_branding', 'api_access', 'support', 'priority_support', 'premium_support',
            'dedicated_account_manager', 'custom_domain', 'custom_email_template', 'multi_currency_payment', 'custom_username', 'web_profile',
            'is_storage_grace_period_allow', 'is_billing_grace_period_allow', 'is_active'];
        $data = $request->validate(
            ['name' => 'required|string|max:100', 'description' => 'nullable|string|max:1000']
            + collect($limits)->mapWithKeys(fn ($f) => [$f => 'nullable|integer|min:0'])->all()
            + collect($features)->mapWithKeys(fn ($f) => [$f => 'boolean'])->all()
        );
        $plan->update($data);

        return response()->json(['status' => true, 'data' => $plan]);
    }

    // Set (or change) a plan's price per member per day in one region
    public function setPrice(Request $request, $id)
    {
        ManagementPackage::findOrFail($id);
        $data = $request->validate([
            'region_id' => 'required|exists:regions,id',
            'price_rate' => 'required|numeric|min:0|max:1000',
        ]);
        DB::table('management_pricings')->updateOrInsert(
            ['management_package_id' => $id, 'region_id' => $data['region_id']],
            ['price_rate' => $data['price_rate'], 'is_active' => 1, 'updated_at' => now(), 'created_at' => now()]
        );

        return response()->json(['status' => true]);
    }

    // ---- Which plan each organisation is on ----

    public function subscriptions()
    {
        $rows = DB::table('users as u')
            ->where('u.type', 'organisation')->whereNull('u.deleted_at')
            ->leftJoin('management_subscriptions as s', fn ($j) => $j->on('s.user_id', '=', 'u.id')->where('s.is_active', 1))
            ->leftJoin('management_packages as p', 'p.id', '=', 's.management_package_id')
            ->orderBy('u.org_name')
            ->get(['u.id as org_id', 'u.org_name', 'u.email', 'u.created_at', 's.id as subscription_id', 's.start_date', 's.subscription_status',
                'p.id as package_id', 'p.name as package_name']);
        $members = DB::table('org_members')->where('is_active', 1)
            ->select('org_type_user_id', DB::raw('count(*) as n'))->groupBy('org_type_user_id')->pluck('n', 'org_type_user_id');

        return response()->json(['status' => true, 'data' => $rows->map(fn ($r) => (array) $r + ['members' => (int) ($members[$r->org_id] ?? 0)])]);
    }
}
