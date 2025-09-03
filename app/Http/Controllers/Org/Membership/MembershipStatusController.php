<?php
namespace App\Http\Controllers\Org\Membership;
use App\Http\Controllers\Controller;

use App\Models\MembershipStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MembershipStatusController extends Controller
{
    /**
     * Display a listing of the membership statuses.
     */
    public function index()
    {
        $statuses = MembershipStatus::all();
        return response()->json([
            'status' => true,
            'message' => 'Membership statuses retrieved successfully.',
            'data' => $statuses
        ]);
    }

    /**
     * Store a newly created membership status in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:30|unique:membership_statuses,name',
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $status = MembershipStatus::create($request->only('name', 'is_active'));

        return response()->json([
            'status' => true,
            'message' => 'Membership status created successfully.',
            'data' => $status
        ]);
    }

    /**
     * Display the specified membership status.
     */
    public function show($id)
    {
        $status = MembershipStatus::find($id);

        if (!$status) {
            return response()->json([
                'status' => false,
                'message' => 'Membership status not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Membership status retrieved successfully.',
            'data' => $status
        ]);
    }

    /**
     * Update the specified membership status in storage.
     */
    public function update(Request $request, $id)
    {
        $status = MembershipStatus::find($id);

        if (!$status) {
            return response()->json([
                'status' => false,
                'message' => 'Membership status not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:30|unique:membership_statuses,name,' . $id,
            'is_active' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $status->update($request->only('name', 'is_active'));

        return response()->json([
            'status' => true,
            'message' => 'Membership status updated successfully.',
            'data' => $status
        ]);
    }

    /**
     * Remove the specified membership status from storage.
     */
    public function destroy($id)
    {
        $status = MembershipStatus::find($id);

        if (!$status) {
            return response()->json([
                'status' => false,
                'message' => 'Membership status not found.'
            ], 404);
        }

        $status->delete();

        return response()->json([
            'status' => true,
            'message' => 'Membership status deleted successfully.'
        ]);
    }
}
