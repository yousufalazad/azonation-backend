<?php

namespace App\Http\Controllers\Org\Report;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class OrgReportController extends Controller
{
    public function getIncomeReport(Request $request)
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subMonths(11)->startOfMonth();

            $incomeData = DB::table('accounts')
                ->select(
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('SUM(amount) as total_income')
                )
                ->where('type', 'income') // Assuming 'type' is the column that differentiates income and expense
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('year', 'month')
                ->orderByRaw('YEAR(date) ASC, MONTH(date) ASC')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $incomeData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching report data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getExpenseReport(Request $request)
    {
        try {
            // Get first day of 11 months ago and last day of current month
            $startDate = Carbon::now()->subMonths(11)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            $expenseData = DB::table('accounts')
                ->select(
                    DB::raw('YEAR(date) as year'),
                    DB::raw('MONTH(date) as month'),
                    DB::raw('SUM(amount) as total_expense')
                )
                ->where('type', 'expense')
                ->whereBetween('date', [$startDate, $endDate])
                ->groupBy('year', 'month')
                ->orderByRaw('YEAR(date), MONTH(date)')
                ->get();

            return response()->json([
                'status' => true,
                'data' => $expenseData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching report data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getMembershipGrowthReport(Request $request)
{
    try {
        $user_id = Auth::id();

        // Get all members for the organisation
        $members = DB::table('org_members')
            ->where('org_type_user_id', $user_id)
            ->whereNotNull('membership_start_date') // Ensure start date exists
            ->get();

        // Prepare last 12 months data
        $result = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;

            // End of current month
            $monthEnd = Carbon::create($year, $month)->endOfMonth();

            // Count all members who joined on or before this month
            $count = $members->filter(function ($member) use ($monthEnd) {
                return Carbon::parse($member->membership_start_date)->lessThanOrEqualTo($monthEnd);
            })->count();

            $result[] = [
                'year' => $year,
                'month' => $month,
                'total_members' => $count,
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $result,
        ]);

    } catch (\Throwable $th) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong.',
            'error' => $th->getMessage(),
        ], 500);
    }
}
}
