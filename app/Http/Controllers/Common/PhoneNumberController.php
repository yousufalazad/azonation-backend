<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use App\Models\PhoneNumber;
use App\Models\DialingCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhoneNumberController extends Controller
{
    public function index()
    {

        $userId = Auth::id();
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
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'dialing_code_id' => 'nullable|integer',
            'phone_number' => 'nullable',
            'phone_type' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        try {
            $userId = Auth::id();

            $phoneNumber = new PhoneNumber();
            $phoneNumber->user_id = $userId;
            $phoneNumber->fill($validatedData);
            $phoneNumber->save();

            return response()->json([
                'status'  => true,
                'data'    => $phoneNumber,
                'message' => 'Org PhoneNumber created successfully.',
            ], 201);
        } catch (\Exception $e) {
            Log::error("Failed to create Org phoneNumber for user ID {$userId}: " . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validatedData = $request->validate([
            'dialing_code_id' => 'nullable|integer',
            'phone_number' => 'nullable',
            'phone_type' => 'nullable|integer',
            'status' => 'nullable|integer',
        ]);

        try {
            $userId = Auth::id();

            // ✅ Correct query — use first() instead of find()
            $phoneNumber = PhoneNumber::where('id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$phoneNumber) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Org PhoneNumber record not found.',
                ], 404);
            }

            $phoneNumber->update($validatedData);

            return response()->json([
                'status'  => true,
                'data'    => $phoneNumber,
                'message' => 'Org PhoneNumber updated successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error("Failed to update Org phoneNumber for user ID {$userId}: " . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 500);
        }
    }
    public function destroy(PhoneNumber $phoneNumber) {}
}
