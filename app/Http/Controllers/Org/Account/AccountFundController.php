<?php
namespace App\Http\Controllers\Org\Account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountFund;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AccountFundController extends Controller
{
    public function index()
    {
        $funds = AccountFund::all();
        return response()->json(['status' => true, 'data' => $funds], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        try {
            $user_id = $request->user()->id;
            Log::info('Creating fund for user ID: ' . $user_id);
            Log::info('Fund data: ', ['name' => $request->name, 'status' => $request->status]);
            $fund = AccountFund::create([
                'user_id' => $user_id,
                'name' => $request->name,
                'status' => $request->status,
            ]);
            return response()->json(['status' => true, 'data' => $fund, 'message' => 'Fund created successfully.'], 201);
        } catch (\Exception $e) {
            Log::error('Error creating fund: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to create fund.'], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'status' => 'required|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }
        $fund = AccountFund::find($id);
        if (!$fund) {
            return response()->json(['status' => false, 'message' => 'Fund not found.'], 404);
        }
        $fund->update([
            'name' => $request->name,
            'status' => $request->status,
        ]);
        return response()->json(['status' => true, 'data' => $fund, 'message' => 'Fund updated successfully.'], 200);
    }
    public function destroy($id)
    {
        $fund = AccountFund::find($id);
        if (!$fund) {
            return response()->json(['status' => false, 'message' => 'Fund not found.'], 404);
        }
        $fund->delete();
        return response()->json(['status' => true, 'message' => 'Fund deleted successfully.'], 200);
    }
}
