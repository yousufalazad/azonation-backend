<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::all();
        return response()->json(['status' => true, 'data' => $countries], 200);
    }
    public function create() {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'iso_code_alpha_3' => 'required',
            'iso_code_alpha_2' => 'required',
            'numeric_code' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            Log::info('Country data: ', ['name' => $request->name, 'iso_code_alpha_3' => $request->iso_code_alpha_3]);
            $country = Country::create([
                'name' => $request->name,
                'iso_code_alpha_3' => $request->iso_code_alpha_3,
                'iso_code_alpha_2' => $request->iso_code_alpha_2,
                'numeric_code' => $request->numeric_code,
                'is_active' => $request->is_active,
            ]);
            return response()->json(['status' => true, 'data' => $country, 'message' => 'Country created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating Country: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create Country.'], 500);
        }
    }
    public function show(Country $country) {}
    public function edit(Country $country) {}
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'iso_code_alpha_3' => 'required',
            'iso_code_alpha_2' => 'required',
            'numeric_code' => 'required',
            'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['status' => false, 'message' => 'Country not found.'], 404);
        }
        $country->update([
            'name' => $request->name,
            'iso_code_alpha_3' => $request->iso_code_alpha_3,
            'iso_code_alpha_2' => $request->iso_code_alpha_2,
            'numeric_code' => $request->numeric_code,
            'is_active' => $request->is_active,
        ]);
        return response()->json(['status' => true, 'data' => $country, 'message' => 'Country updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return response()->json(['status' => false, 'message' => 'Country not found.'], 404);
        }
        $country->delete();
        return response()->json(['status' => true, 'message' => 'Country deleted successfully.'], 200);
    }
}
