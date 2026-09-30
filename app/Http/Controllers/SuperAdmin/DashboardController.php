<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// The Super Admin home page: how the platform is doing, and what needs attention
class DashboardController extends Controller
{
    public function overview()
    {
        $monthStart = Carbon::now()->startOfMonth();

        $accounts = DB::table('users')->whereNull('deleted_at')
            ->select('type', DB::raw('count(*) as n'))->groupBy('type')->pluck('n', 'type');

        $newThisMonth = DB::table('users')->whereNull('deleted_at')->where('created_at', '>=', $monthStart)
            ->select('type', DB::raw('count(*) as n'))->groupBy('type')->pluck('n', 'type');

        $plans = DB::table('management_subscriptions as s')
            ->join('management_packages as p', 'p.id', '=', 's.management_package_id')
            ->where('s.is_active', 1)
            ->select('p.name', DB::raw('count(*) as n'))->groupBy('p.name')->orderByDesc('n')->get();

        $unpaid = DB::table('invoices')->where('is_published', 1)->where('balance_due', '>', 0)
            ->whereNotIn('payment_status', ['paid', 'cancelled', 'refunded'])
            ->select('currency_code', DB::raw('count(*) as n'), DB::raw('sum(balance_due) as total'))
            ->groupBy('currency_code')->get();

        $overdue = DB::table('invoices')->where('is_published', 1)->where('balance_due', '>', 0)
            ->whereNotIn('payment_status', ['paid', 'cancelled', 'refunded'])
            ->whereDate('due_date', '<', Carbon::today())->count();

        $recentOrgs = DB::table('users')->whereNull('deleted_at')->where('type', 'organisation')
            ->orderByDesc('created_at')->limit(5)->get(['id', 'org_name', 'email', 'created_at']);

        return response()->json([
            'status' => true,
            'data' => [
                'organisations' => (int) ($accounts['organisation'] ?? 0),
                'people' => (int) ($accounts['individual'] ?? 0),
                'pending_signups' => (int) ($accounts['pending'] ?? 0),
                'new_organisations' => (int) ($newThisMonth['organisation'] ?? 0),
                'new_people' => (int) ($newThisMonth['individual'] ?? 0),
                'open_support' => DB::table('support_requests')->where('status', 'open')->count(),
                'unpaid_invoices' => $unpaid,
                'overdue_invoices' => $overdue,
                'plans' => $plans,
                'recent_organisations' => $recentOrgs,
            ],
        ]);
    }
}
