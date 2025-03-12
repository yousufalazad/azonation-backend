<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

use App\Models\RegionalTaxRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RegionalTaxRateController extends Controller
{
    public function index()
    {
        $regionalTaxRate = RegionalTaxRate::select('regional_tax_rates.*', 'regions.name as region_name')
            ->leftJoin('regions', 'regional_tax_rates.region_id', '=', 'regions.id')
            ->get();
        return response()->json(['status' => true, 'data' => $regionalTaxRate], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tax_rate' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Region Currency data: ', ['tax_rate' => $request->tax_rate, 'region_id' => $request->region_id]);
            $regionalTaxRate = RegionalTaxRate::create([
                'tax_rate' => $request->tax_rate,
                'region_id' => $request->region_id,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $regionalTaxRate, 'message' => 'Region Currency created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Region Currency.'], 500);
        }
    }
    public function show(RegionalTaxRate $regionalTaxRate) {}
    public function edit(RegionalTaxRate $regionalTaxRate) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tax_rate' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $regionalTaxRate = RegionalTaxRate::find($id);
        if (!$regionalTaxRate) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }
        $regionalTaxRate->update([
            'tax_rate' => $request->tax_rate,
            'region_id' => $request->region_id,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $regionalTaxRate, 'message' => 'Region Currency updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $regionalTaxRate = RegionalTaxRate::find($id);
        if (!$regionalTaxRate) {
            return response()->json(['status' => false, 'message' => 'Region Currency not found.'], 404);
        }
        $regionalTaxRate->delete();
        return response()->json(['status' => true, 'message' => 'Region Currency deleted successfully.'], 200);
    }
}
