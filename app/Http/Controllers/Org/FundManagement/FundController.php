<?php
namespace App\Http\Controllers\Org\FundManagement;

use App\Http\Concerns\ResolvesCurrentOrg;
// use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller;

use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FundController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:fund.read')->only(['index', 'show']);
        $this->middleware('org.permission:fund.create')->only(['create', 'store']);
        $this->middleware('org.permission:fund.update')->only(['edit', 'update']);
        $this->middleware('org.permission:fund.delete')->only(['destroy']);
    }
    public function index()
    {
        $userId = $this->orgIdOrFail();
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
        // The organisation always comes from the session, never from the form
        $request->merge(['user_id' => $this->orgIdOrFail()]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            $userId = $this->orgIdOrFail();
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
        // The organisation always comes from the session, never from the form
        $request->merge(['user_id' => $this->orgIdOrFail()]);

        // dd($request->all());exit;
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], status: 422);
        }
        $fund = $this->owned(Fund::class)->find($id); // only this organisation's funds
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
        $fund = $this->owned(Fund::class)->find($id); // only this organisation's funds
        if (!$fund) {
            return response()->json(['status' => false, 'message' => 'Fund not found.'], 404);
        }
        $fund->delete();
        return response()->json(['status' => true, 'message' => 'Fund deleted successfully.'], 200);
    }
}