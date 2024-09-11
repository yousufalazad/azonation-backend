<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

use App\Models\Address;
use Illuminate\Http\Request;


class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

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
        // $request->validate([
        //     'user_id' => 'required',
        // ]);

        // // Create a new committee record associated with the organisation
        // Address::create([
        //     'user_id' => $request->user_id,
        //     'address_line_one' => $request->address_line_one,
        //     'address_line_two' => $request->address_line_two,
        //     'city' => $request->city,
        //     'state_or_region' => $request->state_or_region,
        //     'postal_code' => $request->postal_code,
        //     'country_id' => $request->country_id,
        // ]);

        // // Return a success response
        // return response()->json(['message' => 'Address created successfully', 200]);
    }

    /**
     * Display the specified resource.
     */
    public function show($userId)
    {
        $address = Address::where('user_id', $userId)->first();

        if ($address) {
            return response()->json([
                'status' => true,
                'data' => $address
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Address not found'
            ], 404);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
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
            'address_line_one' => 'nullable|string',
            'address_line_two' => 'nullable|string',
            'city' => 'nullable|string',
            'state_or_region' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country_id' => 'nullable',
        ]);

        try {
            // Find the address profile by user ID
            $address = Address::firstOrNew(['user_id' => $userId]);

            // Update or create new profile based on existence
            $address->fill($validatedData);
            $address->save();

            $message = $address->wasRecentlyCreated
                ? 'Org profile created successfully.'
                : 'Org profile updated successfully.';

            // Return a JSON response
            return response()->json([
                'status'  => true,
                'data'    => $address,
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
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        //
    }
}
