<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
        ]);
        Address::create([
            'user_id' => $request->user_id,
            'address_line_one' => $request->address_line_one,
            'address_line_two' => $request->address_line_two,
            'city' => $request->city,
            'state_or_region' => $request->state_or_region,
            'postal_code' => $request->postal_code,
            'country_id' => $request->country_id,
        ]);
        $message = 'Address created successfully';
        return response()->json([
            'status'  => true,
            'message' => $message,
        ], 200);
    }
    public function show($userId)
    {
        $address = Address::where('addresses.user_id', $userId)
            ->leftJoin('countries', 'addresses.country_id', '=', 'countries.id')
            ->select(
                'addresses.*',
                'countries.country_name'
            )
            ->first();
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
    public function edit(Address $address) {}
    public function update(Request $request, int $userId): JsonResponse
    {
        $validatedData = $request->validate([
            'address_line_one' => 'nullable|string',
            'address_line_two' => 'nullable|string',
            'city' => 'nullable|string',
            'state_or_region' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'country_id' => 'nullable',
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
