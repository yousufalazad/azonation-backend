<?php
namespace App\Http\Controllers\Org\Committee;
use App\Http\Controllers\Controller;

use App\Models\Committee;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function getCommitteeListByUserId($userId)
    {
        $committeeList = Committee::where('user_id', $userId)
            ->orderBy('id', 'asc')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $committeeList
        ]);
    }
    public function index() {}
    public function create() {}
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        Committee::create([
            'user_id' => $request->user_id,
            'name' => $request->name,
            'short_description' => $request->short_description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'note' => $request->note,
            'status' => $request->status,
        ]);
        return response()->json(['message' => 'Committee created successfully', 200]);
    }
    public function show(Committee $committee) {}
    public function edit(Committee $committee) {}
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'short_description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'note' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        $committee = Committee::where('id', $id)->first();
        if (!$committee) {
            return response()->json(['message' => 'Committee not found'], 404);
        }
        $committee->update([
            'name' => $validatedData['name'],
            'short_description' => $validatedData['short_description'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'note' => $validatedData['note'],
            'status' => $validatedData['status'],
        ]);
        return response()->json(['message' => 'Committee updated successfully'], 200);
    }
    public function destroy($id)
    {
        try {
            $committee = Committee::findOrFail($id);
            $committee->delete();
            return response()->json([
                'status' => true,
                'message' => 'Committee deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.'
            ], 500);
        }
    }
}
