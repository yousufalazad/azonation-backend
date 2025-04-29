<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Management;
use App\Http\Controllers\Controller;

use App\Models\ManagementPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ManagementPricingController extends Controller
{
    public function index() {
        try {
            $userId = Auth::id();
            $user = User::with(['userCountry.country.region', 'managementSubscription.managementPackage'])->findOrFail($userId);
            $region = $user->userCountry->country->region->region;
            $managementPackage = $user->managementSubscription->managementPackage;
            $managementPriceRate = ManagementPricing::where('region_id', $region->id)
                ->where('management_package_id', $managementPackage->id)
                ->value('price_rate');
            if ($managementPriceRate) {
                return response()->json([
                    'daily_price_rate' => $managementPriceRate,
                ]);
            } else {
                return response()->json([
                    'error' => 'Price rate not found for the user\'s region and package',
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the daily price rate',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function create() {}
    public function store(Request $request) {}
    public function show(ManagementPricing $managementPricing) {}
    public function edit(ManagementPricing $managementPricing) {}
    public function update(Request $request, ManagementPricing $managementPricing) {}
    public function destroy(ManagementPricing $managementPricing) {}
}
