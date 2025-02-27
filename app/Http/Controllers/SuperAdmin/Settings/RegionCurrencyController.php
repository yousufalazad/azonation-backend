<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\RegionCurrency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RegionCurrencyController extends Controller
{
    public function index()
    {
        $regionCurrency = RegionCurrency::select('region_currencies.*', 'regions.name as region_name', 'currencies.name as currency_name')
            ->leftJoin('regions', 'region_currencies.region_id', '=', 'regions.id')
            ->leftJoin('currencies', 'region_currencies.currency_id', '=', 'currencies.id')
            ->get();
        return response()->json(['status' => true, 'data' => $regionCurrency], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Region Currency data: ', ['currency_id' => $request->currency_id, 'region_id' => $request->region_id]);
            $regionCurrency = RegionCurrency::create([
                'currency_id' => $request->currency_id,
                'region_id' => $request->region_id,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $regionCurrency, 'message' => 'Region Currency created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Region Currency.'], 500);
        }
    }
    public function show(RegionCurrency $regionCurrency) {}
    public function edit(RegionCurrency $regionCurrency) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'currency_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $regionCurrency = RegionCurrency::find($id);
        if (!$regionCurrency) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }
        $regionCurrency->update([
            'currency_id' => $request->currency_id,
            'region_id' => $request->region_id,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $regionCurrency, 'message' => 'Region Currency updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $regionCurrency = RegionCurrency::find($id);
        if (!$regionCurrency) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }
        $regionCurrency->delete();
        return response()->json(['status' => true, 'message' => 'Region Currency deleted successfully.'], 200);
    }
}
