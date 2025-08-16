<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\PhoneNumber;
use App\Models\DialingCode;
use Illuminate\Http\Request;

class PhoneNumberController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show($userId)
    {
        $PhoneNumber = PhoneNumber::where('phone_numbers.user_id', $userId)
            ->leftJoin('dialing_codes', 'phone_numbers.dialing_code_id', '=', 'dialing_codes.id')
            ->select(
                'phone_numbers.*',
                'dialing_codes.dialing_code'
            )->first();
        // $PhoneNumber = PhoneNumber::where('user_id', $userId)->first();
        return response()->json([
            'status' => true,
            'data' => $PhoneNumber
        ]);
    }
    public function edit(PhoneNumber $phoneNumber) {}
    public function getAllDialingCodes()
    {
        $allDialingCodes = DialingCode::leftJoin('countries', 'dialing_codes.country_id', '=', 'countries.id')
            ->select(
                'dialing_codes.country_id',
                'dialing_codes.dialing_code',
                'countries.country_name'
            )
            ->get();
        return response()->json([
            'status'  => true,
            'data'    => $allDialingCodes,
        ], 200);
    }
    public function update(Request $request, int $userId): JsonResponse
    {
        $validatedData = $request->validate([
            'dialing_code_id' => 'nullable|integer',
            'phone_number' => 'nullable',
            'phone_type' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);
        try {
            $phoneNumber = PhoneNumber::firstOrNew(['user_id' => $userId]);
            $phoneNumber->fill($validatedData);
            $phoneNumber->save();
            $message = $phoneNumber->wasRecentlyCreated
                ? 'Org PhoneNumber created successfully.'
                : 'Org PhoneNumber updated successfully.';
            return response()->json([
                'status'  => true,
                'data'    => $phoneNumber,
                'message' => $message,
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update or create Org phoneNumber for user ID {$userId}: " . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }
    public function destroy(PhoneNumber $phoneNumber) {}
}
