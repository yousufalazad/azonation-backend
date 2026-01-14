<?php

namespace App\Http\Controllers\Common;

use id;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AddressController extends Controller
{

    public function getAddressFormat(Request $request)
    {
        // $userId = auth()->id();
        $userId = Auth::id();

        $countryId = DB::table('user_countries')
            ->where('user_id', $userId)
            ->value('country_id');

        $group = DB::table('address_country_groups')
            ->where('country_id', $countryId)
            ->first();

        if (!$group) {
            abort(404, 'Address group not found');
        }

        $format = DB::table('address_group_formats')
            ->where('address_group_id', $group->address_group_id)
            ->where('is_active', 1)
            ->first();


        return response()->json([
            'countryId' => $countryId,
            'group'       => $group,
            'group_alias' => $group->address_group_alias,
            'format'      => json_decode($format->format_components, true),
        ]);
    }

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
    /**
     * Shared save logic
     */
    private function saveAddress(Request $request, $id = null): JsonResponse
    {
        $userId = Auth::id();

        try {
            $validated = $request->validate([
                'address_line_one' => 'nullable|string',
                'address_line_two' => 'nullable|string',
                'city'             => 'nullable|string',
                'state_or_region'  => 'nullable|string',
                'postal_code'      => 'nullable|string',
                'components'       => 'nullable|array',
            ]);

            // 🔑 Decide create or update
            if ($id) {
                // ✅ UPDATE
                $address = Address::where('id', $id)
                    ->where('user_id', $userId)
                    ->firstOrFail();
            } else {
                // ✅ CREATE
                $address = new Address();
                $address->user_id = $userId;
            }

            $address->fill([
                'address_line_one' => $validated['address_line_one'] ?? null,
                'address_line_two' => $validated['address_line_two'] ?? null,
                'city'             => $validated['city'] ?? null,
                'state_or_region'  => $validated['state_or_region'] ?? null,
                'postcode'         => $validated['postal_code'] ?? null,
                'components'       => $validated['components'] ?? null,
            ]);

            $address->save();

            return response()->json([
                'status'  => true,
                'data'    => $address,
                'message' => $id
                    ? 'Address updated successfully.'
                    : 'Address created successfully.',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'status'  => false,
                'message' => 'Address not found or unauthorized.',
            ], 404);
        } catch (\Exception $e) {

            Log::error("Address save failed for user {$userId}: {$e->getMessage()}");

            return response()->json([
                'status'  => false,
                'message' => 'An error occurred. Please try again.',
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        return $this->saveAddress($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->saveAddress($request, $id);
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
    // public function store(Request $request)
    // {
    //     try {
    //         $validatedData = $request->validate([
    //             'address_line_one' => 'required|string',
    //             'address_line_two' => 'nullable|string',
    //             'city' => 'required|string',
    //             'state_or_region' => 'nullable|string',
    //             'postal_code' => 'required|string',
    //         ]);
    //         // $userId = auth()->user()->id;
    //         $userId = Auth::id();

    //         $address = Address::firstOrNew(['user_id' => $userId]);
    //         $address->fill($validatedData);
    //         $address->save();
    //         return response()->json([
    //             'status'  => true,
    //             'data'    => $address,
    //             'message' => 'Address created/updated successfully.',
    //         ], 200);
    //     } catch (\Exception $e) {
    //         Log::error("Failed to create or update address for user ID {$userId}: " . $e->getMessage());
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'An error occurred while processing your request. Please try again later.',
    //         ], 500);
    //     }
    // }
    // public function update(Request $request, int $userId): JsonResponse
    // {
    //     $validatedData = $request->validate([
    //         'address_line_one' => 'nullable|string',
    //         'address_line_two' => 'nullable|string',
    //         'city' => 'nullable|string',
    //         'state_or_region' => 'nullable|string',
    //         'postal_code' => 'nullable|string',
    //     ]);
    //     try {
    //         $address = Address::firstOrNew(['user_id' => $userId]);
    //         $address->fill($validatedData);
    //         $address->save();
    //         $message = $address->wasRecentlyCreated
    //             ? 'Org profile created successfully.'
    //             : 'Org profile updated successfully.';
    //         return response()->json([
    //             'status'  => true,
    //             'data'    => $address,
    //             'message' => $message,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         Log::error("Failed to update or create OrgProfile for user ID {$userId}: " . $e->getMessage());
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'An error occurred while processing your request. Please try again later.',
    //         ], 500);
    //     }
    // }

    public function destroy(Address $address) {}

}
