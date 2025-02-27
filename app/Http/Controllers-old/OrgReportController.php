<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrgReportController extends Controller
{
    public function getIncomeReport(Request $request)
    {
        try {
            // Get the current date and subtract 12 months
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subMonths(12);

            // Fetch income data for the past 12 months grouped by year and month
            $incomeData = DB::table('org_accounts')
                ->select(
                    DB::raw('YEAR(transaction_date) as year'),
                    DB::raw('MONTH(transaction_date) as month'),
                    DB::raw('SUM(transaction_amount) as total_income')
                )
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->groupBy('year', 'month')
                ->orderByRaw('YEAR(transaction_date) ASC, MONTH(transaction_date) ASC') // Order by year and month
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
        // Get the current date and subtract 12 months
        $endDate = Carbon::now();             
        $startDate = $endDate->copy()->subMonths(12);

        // Fetch expense data for the past 12 months grouped by year and month
        $expenseData = DB::table('org_accounts')
            ->select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('SUM(transaction_amount) as total_expense') // Sum for expenses
            )
            ->where('transaction_type', 'expense') // Filter for expenses
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupBy('year', 'month')
            ->orderByRaw('YEAR(transaction_date) ASC, MONTH(transaction_date) ASC') // Order by year and month
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

}
