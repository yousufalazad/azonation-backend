<?php

namespace App\Http\Controllers;

use App\Models\OrgProfile;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OrgProfileController extends Controller
{
    protected function success($message, $data = [], $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(OrgProfile $orgProfile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgProfile $orgProfile)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $userId): JsonResponse
    {
        // Validate the request data
        $validatedData = $request->validate([
            'short_description'  => 'nullable|string|max:255',
            'detail_description' => 'nullable|string',
        ]);

        try {
            // Find the organization profile by user ID
        $orgProfile = OrgProfile::firstOrNew(['user_id' => $userId]);

        // Update or create new profile based on existence
        $orgProfile->fill($validatedData);
        $orgProfile->save();

        $message = $orgProfile->wasRecentlyCreated 
            ? 'Org profile created successfully.' 
            : 'Org profile updated successfully.';

        // Return a JSON response
        return response()->json([
            'status'  => true,
            'data'    => $orgProfile,
            'message' => $message,
        ], 200);

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Failed to update or create OrgProfile for user ID {$userId}: " . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }

        // $request->validate([
        //     'short_description' => 'nullable|string',
        //     'detail_description' => 'nullable|string',
        // ]);

        // $OrgProfile = OrgProfile::where('user_id', $userId)->first();
        // if ($OrgProfile) {
        //     // Update a new orgProfile
        //     $OrgProfile->update($request->all());
        //     return response()->json([
        //         'status' => true,
        //         'data' => $OrgProfile,
        //         'message' => 'Org profile update successfully',
        //     ]);
        // } else {
        //     // Create a new orgProfile
        //     OrgProfile::create([
        //         'short_description' => $request->short_description,
        //         'detail_description' => $request->detail_description,
        //     ]);
            
        //     return response()->json([
        //         'status' => true,
        //         'data' => $OrgProfile,
        //         'message' => 'Org profile created successfully',
        //     ]);
        // }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgProfile $orgProfile)
    {
        //
    }
}
