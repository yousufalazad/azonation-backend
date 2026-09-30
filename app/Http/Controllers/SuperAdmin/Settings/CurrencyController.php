<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => Currency::all()
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency_name' => 'required|string|max:255',
            'currency_code' => 'required|string|size:3|unique:currencies,currency_code',
            'currency_symbol' => 'required|string|max:5',
            'unit_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $currency = Currency::create($validator->validated());
        return response()->json([
            'status' => true,
            'message' => 'Currency created successfully.',
            'data' => $currency
        ]);
    }
    public function update(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'currency_name' => 'required|string|max:255',
            'currency_code' => 'required|string|size:3|unique:currencies,currency_code,' . $currency->id . '',
            'currency_symbol' => 'required|string|max:5',
            'unit_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $currency->update($validator->validated());
        return response()->json([
            'status' => true,
            'message' => 'Currency updated successfully.',
            'data' => $currency
        ]);
    }
    public function destroy($id)
    {
        $currency = Currency::findOrFail($id);
        $currency->delete();
        return response()->json([
            'status' => true,
            'message' => 'Currency deleted successfully.'
        ]);
    }
}
