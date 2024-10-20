<?php

namespace App\Http\Controllers;

use App\Models\OrgAccount;
use App\Models\OrgReport;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrgReportController extends Controller
{
    public function index()
{
    try {
        // Get today's date and date 12 months ago
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();

        // Fetch the total income per month over the past 12 months
        $incomeReports = OrgAccount::selectRaw('YEAR(`transaction_date`) as year, MONTH(`transaction_date`) as month, SUM(`transaction_amount`) as total_income')
            ->where('transaction_type', 'income')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->groupByRaw('YEAR(`transaction_date`), MONTH(`transaction_date`)')
            ->orderByRaw('YEAR(`transaction_date`), MONTH(`transaction_date`)')
            ->get();

        // If data is successfully fetched, return it with a success status
        return response()->json([
            'status' => true,
            'data' => $incomeReports,
        ], 200);

    } catch (\Exception $e) {
        // Handle any errors and return a failure response
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch income report data.',
            'error' => $e->getMessage(), // Optionally, include the exception message for debugging
        ], 500);
    }
}

}
