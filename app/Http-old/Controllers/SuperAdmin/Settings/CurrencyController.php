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
            'name' => 'required|string|max:255',
            'currency_code' => 'required|string|size:3|unique:currencies',
            'symbol' => 'required|string|max:3',
            'unit_name' => 'required|string|max:255',
            'status' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $currency = Currency::create($request->all());
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
            'name' => 'required|string|max:255',
            'currency_code' => 'required|string|size:3|unique:currencies,currency_code,' . $currency->id,
            'symbol' => 'required|string|max:3',
            'unit_name' => 'required|string|max:255',
            'status' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $currency->update($request->all());
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
