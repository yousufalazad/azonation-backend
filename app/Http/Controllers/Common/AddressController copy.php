<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        // $userId = auth()->user()->id;
        $userId = Auth::id();
        // return $address = Cache::remember('user_address_' . $userId, 60, function () use ($userId) {
        //     Address::where('user_id', $userId)->first();
        // });
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
    //     $address = Address::where('addresses.user_id', $userId)
    //         ->leftJoin('countries', 'addresses.country_id', '=', 'countries.id')
    //         ->select(
    //             'addresses.*',
    //             'countries.name'
    //         )
    //         ->first();
    //     if ($address) {
    //         return response()->json([
    //             'status' => true,
    //             'data' => $address
    //         ]);
    //     } else {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Address not found'
    //         ], 404);
    //     }
    // }
    public function create() {}
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'address_line_one' => 'required|string',
                'address_line_two' => 'nullable|string',
                'city' => 'required|string',
                'state_or_region' => 'nullable|string',
                'postal_code' => 'required|string',
            ]);
            // $userId = auth()->user()->id;
            $userId = Auth::id();

            $address = Address::firstOrNew(['user_id' => $userId]);
            $address->fill($validatedData);
            $address->save();
            return response()->json([
                'status'  => true,
                'data'    => $address,
                'message' => 'Address created/updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to create or update address for user ID {$userId}: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }
    
    public function edit(Address $address) {}
    public function update(Request $request, int $userId): JsonResponse
    {
        $validatedData = $request->validate([
            'address_line_one' => 'nullable|string',
            'address_line_two' => 'nullable|string',
            'city' => 'nullable|string',
            'state_or_region' => 'nullable|string',
            'postal_code' => 'nullable|string',
        ]);
        try {
            $address = Address::firstOrNew(['user_id' => $userId]);
            $address->fill($validatedData);
            $address->save();
            $message = $address->wasRecentlyCreated
                ? 'Org profile created successfully.'
                : 'Org profile updated successfully.';
            return response()->json([
                'status'  => true,
                'data'    => $address,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update or create OrgProfile for user ID {$userId}: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }
    public function destroy(Address $address) {}
}
