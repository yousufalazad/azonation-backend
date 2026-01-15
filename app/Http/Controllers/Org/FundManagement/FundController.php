<?php
namespace App\Http\Controllers\Org\FundManagement;
use App\Http\Controllers\Controller;

use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FundController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $funds = Fund::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($funds->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No funds found.'], 404);
        }
        return response()->json(['status' => true, 'data' => $funds], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            $userId = Auth::id();
            $fund = Fund::create([
                'user_id' => $userId,
                'name' => $request->name,
                'is_active' => $request->is_active ?? true, // Default to true if not provided
            ]);
            return response()->json(['status' => true, 'data' => $fund, 'message' => 'Fund created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating fund: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create fund.'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        // dd($request->all());exit;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], status: 422);
        }
        $fund = Fund::find($id);
        if (!$fund) {
            return response()->json(['status' => false, 'message' => 'Fund not found.'], status: 404);
        }
        $fund->update([
            'name' => $request->name,
            'is_active' => $request->is_active ?? 1,
        ]); 
        return response()->json(['status' => true, 'data' => $fund, 'message' => 'Fund updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $fund = Fund::find($id);
        if (!$fund) {
            return response()->json(['status' => false, 'message' => 'Fund not found.'], 404);
        }
        $fund->delete();
        return response()->json(['status' => true, 'message' => 'Fund deleted successfully.'], 200);
    }
}