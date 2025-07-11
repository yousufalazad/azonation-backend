<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\OrgAdministrator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OrgAdministratorController extends Controller
{

    public function index(Request $request)
    {
        $userId = Auth::id();
        $administrators = OrgAdministrator::with(['individualUser', 'administratorProfileImage'])
            ->where('org_type_user_id', $userId)
            ->get();

        $administrators->map(function ($admin) {
            $admin->image_url = $admin->administratorProfileImage && $admin->administratorProfileImage->image_path
                ? url(Storage::url($admin->administratorProfileImage->image_path))
                : null;
            unset($admin->administratorProfileImage);
            return $admin;
        });

        return response()->json($administrators);
    }

    public function checkAdministratorExists(Request $request)
    {
        $validated = $request->validate([
            'org_type_user_id' => 'required|integer|exists:users,id',
            'individual_type_user_id' => 'required|integer|exists:users,id',
        ]);

        $exists = OrgAdministrator::where('org_type_user_id', $validated['org_type_user_id'])
            ->where('individual_type_user_id', $validated['individual_type_user_id'])
            ->where('is_active', 1)
            ->exists();

        return response()->json([
            'status' => true,
            'data' => [
                'exists' => $exists
            ]
        ]);
    }

    // public function getPrimaryAdministrator(Request $request)
    // {
    //     try {
    //         $userId = Auth::id();
    //         $primaryAdmin = OrgAdministrator::with(['individualUser'])
    //             ->where('org_type_user_id', $userId)
    //             ->where('is_primary', 1)
    //             ->where('is_active', 1)
    //             ->first();

    //             $administrators = $primaryAdmin->map(function ($administrator) {
    //             $administrator->image_url = $administrator->administratorProfileImage && $administrator->administratorProfileImage->image_path
    //                 ? url(Storage::url($administrator->administratorProfileImage->image_path))
    //                 : null;
    //             unset($member->administratorProfileImage);
    //             return $administrator;
    //         });

    //         if (!$primaryAdmin) {
    //             return response()->json(['message' => 'No primary administrator found.'], 404);
    //         }

    //         return response()->json($primaryAdmin);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to retrieve primary administrator.'], 500);
    //     }
    // }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'individual_type_user_id' => 'required|exists:users,id',
            // 'start_date' => 'nullable|date',
            'admin_note' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
            'is_active' => 'nullable|boolean'
        ]);

        DB::transaction(function () use ($validated) {
            $userId = Auth::id();
            $today = Carbon::now()->toDateString();

            // Set is_primary = 0 and end_date = today for all current primary administrators only
            OrgAdministrator::where('org_type_user_id', $userId)
                ->where('is_primary', 1)
                ->update([
                    'is_primary' => 0,
                    'end_date' => $today
                ]);

            OrgAdministrator::create([
                'org_type_user_id' => $userId,
                'individual_type_user_id' => $validated['individual_type_user_id'],
                'start_date' => $today,
                'admin_note' => $validated['admin_note'] ?? null,
                'is_primary' => $validated['is_primary'] ?? 1,
                'is_active' => $validated['is_active'] ?? 1,
            ]);
        });

        return response()->json(['message' => 'Administrator added successfully.'], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'individual_type_user_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'admin_note' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $admin = OrgAdministrator::findOrFail($id);

        if (!empty($validated['end_date'])) {
            $startDate = $validated['start_date'] ?? $admin->start_date;
            $endDate = $validated['end_date'];

            // if (Carbon::parse($endDate)->gt(Carbon::now())) {
            //     return response()->json(['message' => 'End date cannot be in the future.'], 422);
            // }

            if (Carbon::parse($endDate)->lt(Carbon::parse($startDate))) {
                return response()->json(['message' => 'End date cannot be before start date.'], 422);
            }
        }

        DB::transaction(function () use ($validated, $admin) {
            if (!empty($validated['is_primary']) && $validated['is_primary'] == 1) {
                OrgAdministrator::where('org_type_user_id', $admin->org_type_user_id)
                    ->where('id', '!=', $admin->id)
                    ->update(['is_primary' => 0]);
            }

            $updateData = [
                'individual_type_user_id' => $validated['individual_type_user_id'],
                'start_date' => $validated['start_date'] ?? $admin->start_date,
                'admin_note' => $validated['admin_note'] ?? $admin->admin_note,
                'is_primary' => $validated['is_primary'] ?? $admin->is_primary,
                'is_active' => $validated['is_active'] ?? $admin->is_active,
            ];

            if (!empty($validated['end_date'])) {
                $updateData['end_date'] = $validated['end_date'];
                $updateData['is_active'] = 0;
            }

            $admin->update($updateData);
        });

        return response()->json(['message' => 'Administrator updated successfully.', 'data' => $admin]);
    }


    public function destroy($id)
    {
        $admin = OrgAdministrator::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Administrator deleted successfully.']);
    }
}
