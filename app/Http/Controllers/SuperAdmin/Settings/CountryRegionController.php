<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\CountryRegion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CountryRegionController extends Controller
{
    public function index()
    {
        $usersCountry = CountryRegion::select('country_regions.*', 'regions.name as region_name', 'countries.name as country_name')
            ->leftJoin('regions', 'country_regions.region_id', '=', 'regions.id')
            ->leftJoin('countries', 'country_regions.country_id', '=', 'countries.id')
            ->get();
        return response()->json(['status' => true, 'data' => $usersCountry], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('User Country data: ', ['country_id' => $request->country_id, 'region_id' => $request->region_id]);
            $dialingCode = CountryRegion::create([
                'country_id' => $request->country_id,
                'region_id' => $request->region_id,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create User Country.'], 500);
        }
    }
    public function show(CountryRegion $countryRegion) {}
    public function edit(CountryRegion $countryRegion) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
            'region_id' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $dialingCode = CountryRegion::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }
        $dialingCode->update([
            'country_id' => $request->country_id,
            'region_id' => $request->region_id,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $dialingCode, 'message' => 'User Country updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $dialingCode = CountryRegion::find($id);
        if (!$dialingCode) {
            return response()->json(['status' => false, 'message' => 'User Country not found.'], 404);
        }
        $dialingCode->delete();
        return response()->json(['status' => true, 'message' => 'User Country deleted successfully.'], 200);
    }
}
