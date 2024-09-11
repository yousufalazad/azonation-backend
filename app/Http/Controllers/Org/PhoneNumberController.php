<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\PhoneNumber;
use Illuminate\Http\Request;

class PhoneNumberController extends Controller
{
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
    public function show($userId)
    {
        $PhoneNumber = PhoneNumber::where('user_id', $userId)->first();
        return response()->json([
            'status' => true,
            'data' => $PhoneNumber
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PhoneNumber $phoneNumber)
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
            'dialing_code_id' => 'nullable|integer',
            'phone_number' => 'nullable|integer',
            'phone_type' => 'nullable',
            'status' => 'nullable|boolean',
            
        ]);

        try {
            // Find the address profile by user ID
            $phoneNumber = PhoneNumber::firstOrNew(['user_id' => $userId]);

            // Update or create new profile based on existence
            $phoneNumber->fill($validatedData);
            $phoneNumber->save();

            $message = $phoneNumber->wasRecentlyCreated
                ? 'Org PhoneNumber created successfully.'
                : 'Org PhoneNumber updated successfully.';

            // Return a JSON response
            return response()->json([
                'status'  => true,
                'data'    => $phoneNumber,
                'message' => $phoneNumber,
            ], 200);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error("Failed to update or create Org phoneNumber for user ID {$userId}: " . $e->getMessage());

            // Return a generic error response
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhoneNumber $phoneNumber)
    {
        //
    }
}
