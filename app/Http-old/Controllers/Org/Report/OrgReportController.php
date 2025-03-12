<?php
namespace App\Http\Controllers\Org\Report;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrgReportController extends Controller
{
    public function getIncomeReport(Request $request)
    {
        try {
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subMonths(12);
            $incomeData = DB::table('org_accounts')
                ->select(
                    DB::raw('YEAR(transaction_date) as year'),
                    DB::raw('MONTH(transaction_date) as month'),
                    DB::raw('SUM(transaction_amount) as total_income')
                )
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->groupBy('year', 'month')
                ->orderByRaw('YEAR(transaction_date) ASC, MONTH(transaction_date) ASC')
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
            $endDate = Carbon::now();
            $startDate = $endDate->copy()->subMonths(12);
            $expenseData = DB::table('org_accounts')
                ->select(
                    DB::raw('YEAR(transaction_date) as year'),
                    DB::raw('MONTH(transaction_date) as month'),
                    DB::raw('SUM(transaction_amount) as total_expense')
                )
                ->where('transaction_type', 'expense')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->groupBy('year', 'month')
                ->orderByRaw('YEAR(transaction_date) ASC, MONTH(transaction_date) ASC')
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
