<?php

namespace App\Http\Controllers\Org\Report;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

/**
 * Reports for the current organisation. All figures are limited to that organisation.
 */
class OrgReportController extends Controller
{
    use ResolvesCurrentOrg;

    // Monthly income for the last 12 months (dashboard chart)
    public function getIncomeReport()
    {
        return response()->json(['status' => true, 'data' => $this->monthlyTotals('income', 'total_income')]);
    }

    // Monthly spending for the last 12 months (dashboard chart)
    public function getExpenseReport()
    {
        return response()->json(['status' => true, 'data' => $this->monthlyTotals('expense', 'total_expense')]);
    }

    // Members at the end of each of the last 12 months (dashboard chart)
    public function getMembershipGrowthReport()
    {
        $starts = DB::table('org_members')
            ->where('org_type_user_id', $this->orgIdOrFail())
            ->whereNotNull('membership_start_date')
            ->pluck('membership_start_date');

        $result = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthEnd = Carbon::now()->subMonthsNoOverflow($i)->endOfMonth();
            $result[] = [
                'year' => $monthEnd->year,
                'month' => $monthEnd->month,
                'total_members' => $starts->filter(fn ($d) => Carbon::parse($d)->lte($monthEnd))->count(),
            ];
        }
        return response()->json(['status' => true, 'data' => $result]);
    }

    /**
     * One report for any period: ?from=2026-01-01&to=2026-12-31
     * Money in and out (by month and by fund), members joined and left,
     * meetings and events held, and how many people came.
     */
    public function summary(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $orgId = $this->orgIdOrFail();
        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();
        if ($from->diffInMonths($to) > 60) {
            return response()->json(['status' => false, 'message' => 'Choose a period of five years or less.'], 422);
        }

        return response()->json(['status' => true, 'data' => [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'finance' => $this->finance($orgId, $from, $to),
            'members' => $this->members($orgId, $from, $to),
            'meetings' => $this->gatherings('meetings', 'meeting_attendances', 'meeting_guest_attendances', 'meeting_id', $orgId, $from, $to),
            'events' => $this->gatherings('events', 'event_attendances', 'event_guest_attendances', 'event_id', $orgId, $from, $to),
        ]]);
    }

    private function monthlyTotals(string $type, string $key)
    {
        $start = Carbon::now()->subMonthsNoOverflow(11)->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        return DB::table('fund_management')
            ->select(DB::raw('YEAR(date) as year'), DB::raw('MONTH(date) as month'), DB::raw("SUM(amount) as $key"))
            ->where('type', $type)
            ->where('user_id', $this->orgIdOrFail())
            ->where('is_active', true)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();
    }

    private function finance(int $orgId, Carbon $from, Carbon $to): array
    {
        $base = fn () => DB::table('fund_management')
            ->where('fund_management.user_id', $orgId)
            ->where('fund_management.is_active', true)
            ->whereBetween('fund_management.date', [$from->toDateString(), $to->toDateString()]);

        $byMonth = $base()
            ->select(
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                DB::raw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Every month in the period, including months with nothing recorded
        $months = [];
        foreach (CarbonPeriod::create($from->copy()->startOfMonth(), '1 month', $to->copy()->startOfMonth()) as $m) {
            $key = $m->format('Y-m');
            $months[] = [
                'month' => $key,
                'income' => (float) ($byMonth[$key]->income ?? 0),
                'expense' => (float) ($byMonth[$key]->expense ?? 0),
            ];
        }

        $byFund = $base()
            ->leftJoin('funds', 'fund_management.fund_id', '=', 'funds.id')
            ->select(
                'funds.name as fund',
                DB::raw("SUM(CASE WHEN fund_management.type = 'income' THEN fund_management.amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN fund_management.type = 'expense' THEN fund_management.amount ELSE 0 END) as expense")
            )
            ->groupBy('funds.name')
            ->get()
            ->map(fn ($r) => ['fund' => $r->fund, 'income' => (float) $r->income, 'expense' => (float) $r->expense])
            ->sortByDesc(fn ($r) => $r['income'] + $r['expense'])
            ->values();

        // Balance of all money recorded before the period
        $before = DB::table('fund_management')
            ->where('user_id', $orgId)->where('is_active', true)
            ->where('date', '<', $from->toDateString())
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as balance")
            ->value('balance');

        $income = array_sum(array_column($months, 'income'));
        $expense = array_sum(array_column($months, 'expense'));
        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'opening_balance' => (float) $before,
            'closing_balance' => (float) $before + $income - $expense,
            'transactions' => $base()->count(),
            'by_month' => $months,
            'by_fund' => $byFund,
        ];
    }

    private function members(int $orgId, Carbon $from, Carbon $to): array
    {
        $members = DB::table('org_members')->where('org_type_user_id', $orgId);
        return [
            'active_now' => (clone $members)->where('is_active', 1)->count(),
            'joined' => (clone $members)->whereBetween('membership_start_date', [$from->toDateString(), $to->toDateString()])->count(),
            'left' => DB::table('membership_terminations')
                ->where('org_type_user_id', $orgId)
                ->whereBetween('terminated_at', [$from, $to])
                ->count(),
        ];
    }

    /**
     * Meetings or events in the period, with members and guests who attended
     * (a status that means they came), and the average per meeting/event.
     */
    private function gatherings(string $table, string $memberTable, string $guestTable, string $key, int $orgId, Carbon $from, Carbon $to): array
    {
        $ids = DB::table($table)
            ->where('user_id', $orgId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('id');

        $attended = fn (string $t) => $ids->isEmpty() ? 0 : DB::table($t)
            ->join('attendance_statuses', "$t.attendance_status_id", '=', 'attendance_statuses.id')
            ->whereIn("$t.$key", $ids)
            ->where('attendance_statuses.is_attended', true)
            ->count();

        $marked = $ids->isEmpty() ? collect() : DB::table($memberTable)->whereIn($key, $ids)->distinct()->pluck($key);
        $members = $attended($memberTable);
        return [
            'held' => $ids->count(),
            'with_attendance' => $marked->count(),
            'members_attended' => $members,
            'guests_attended' => $attended($guestTable),
            // Average only over meetings/events where attendance was taken
            'average_members' => $marked->count() ? round($members / $marked->count(), 1) : null,
        ];
    }
}
