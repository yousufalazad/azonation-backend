<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Controllers\Controller;
use App\Mail\AddMemberSuccessMail;
use Illuminate\Support\Facades\Mail;
use App\Models\OrgMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Notifications\MemberAddSuccessful;
use App\Models\User;
use App\Notifications\AddMemberSuccess;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;


class OrgMemberController extends Controller
{
    use Notifiable;
    public function getOrgAllMemberName(Request $request)
    {
        $userId = Auth::id();
        $getOrgAllMemberName = OrgMember::with(['individual:id,first_name,last_name', 'membershipType',])
            ->where('org_type_user_id', $userId)
            ->where('is_active', '1')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $getOrgAllMemberName
        ]);
    }

    public function index(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString(); // get current date in YYYY-MM-DD format

        $getOrgAllMembers = OrgMember::with(['individual.phoneNumber', 'membershipStatus', 'membershipType', 'memberProfileImage'])
            ->where('org_type_user_id', $userId)
            ->get();

        $getOrgAllMembers = $getOrgAllMembers->map(function ($member) {
            $member->image_url = $member->memberProfileImage && $member->memberProfileImage->image_path
                ? url(Storage::url($member->memberProfileImage->image_path))
                : null;
            unset($member->memberProfileImage);
            return $member;
        });

        return response()->json([
            'status' => true,
            'data' => $getOrgAllMembers
        ]);
    }

    public function X_index(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString(); // get current date in YYYY-MM-DD format

        $getOrgAllMembers = OrgMember::with(['individual.phoneNumber', 'membershipType', 'memberProfileImage'])
            ->where('org_type_user_id', $userId)
            ->where(function ($query) use ($today) {
                $query->whereNull('membership_end_date') // check for NULL
                    ->orWhere('membership_end_date', '>=', $today); // not expired
            })
            ->get();

        $getOrgAllMembers = $getOrgAllMembers->map(function ($member) {
            $member->image_url = $member->memberProfileImage && $member->memberProfileImage->image_path
                ? url(Storage::url($member->memberProfileImage->image_path))
                : null;
            unset($member->memberProfileImage);
            return $member;
        });

        return response()->json([
            'status' => true,
            'data' => $getOrgAllMembers
        ]);
    }

    public function getOrgFormerMembers(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::today()->toDateString(); // get current date in YYYY-MM-DD format

        $getOrgAllMembers = OrgMember::with(['individual', 'membershipType', 'memberProfileImage'])
            ->where('org_type_user_id', $userId)
            ->where('is_active', '1')
            ->where(function ($query) use ($today) {
                $query->whereNotNull('membership_end_date') // must have an end date
                    ->where('membership_end_date', '<=', $today); // expired
            })
            ->get();

        $getOrgAllMembers = $getOrgAllMembers->map(function ($member) {
            $member->image_url = $member->memberProfileImage && $member->memberProfileImage->image_path
                ? url(Storage::url($member->memberProfileImage->image_path))
                : null;
            unset($member->memberProfileImage);
            return $member;
        });

        return response()->json([
            'status' => true,
            'data' => $getOrgAllMembers
        ]);
    }


    // public function getOrgMembers($userId)
    // {
    //     $members = OrgMember::where('org_type_user_id', $userId)
    //         ->with('individual')
    //         ->get();
    //     return response()->json([
    //         'status' => true,
    //         'data' => $members
    //     ]);
    // }

    public function totalOrgMemberCount(Request $request)
    {
        $userId = Auth::id();
        $totalOrgMemberCount = OrgMember::where('org_type_user_id', $userId)->count();
        return response()->json([
            'status' => true,
            'data' => $totalOrgMemberCount
        ]);
    }

    public function thisYearNewMemberCount(Request $request)
    {
        $userId = Auth::id();
        $thisYearNewMemberCount = OrgMember::where('org_type_user_id', $userId)
            ->whereYear('created_at', date('Y'))
            ->count();
        return response()->json([
            'status' => true,
            'data' => $thisYearNewMemberCount
        ]);
    }
    public function thisMonthNewMemberCount(Request $request)
    {
        $userId = Auth::id();
        $thisMonthNewMemberCount = OrgMember::where('org_type_user_id', $userId)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('m'))
            ->count();
        return response()->json([
            'status' => true,
            'data' => $thisMonthNewMemberCount
        ]);
    }


    public function X_search(Request $request)
    {
        $query = $request->input('query');
        $results = User::where('type', 'individual')
            ->where(function ($q) use ($query) {
                $q->where('azon_id', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhereRaw("CONCAT(dialing_codes.dialing_code, phone_numbers.phone_number) LIKE ?", ["%{$query}%"]);
            })
            ->leftJoin('addresses', 'addresses.user_id', '=', 'users.id')
            // ->leftJoin('countries', 'countries.id', '=', 'addresses.country_id')
            ->leftJoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id')
            ->leftJoin('dialing_codes', 'dialing_codes.id', '=', 'phone_numbers.dialing_code_id')
            ->select(
                'users.*',
                'addresses.city',
                // 'countries.name as country_name',
                'dialing_codes.dialing_code',
                'phone_numbers.phone_number'
            )
            ->get();
        return response()->json([
            'status' => true,
            'data' => $results
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        $results = User::where('type', 'individual')
            ->where(function ($q) use ($query) {
                $q->where('azon_id', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhereRaw("CONCAT(dialing_codes.dialing_code, phone_numbers.phone_number) LIKE ?", ["%{$query}%"]);
            })
            ->leftJoin('addresses', 'addresses.user_id', '=', 'users.id')
            ->leftJoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id')
            ->leftJoin('dialing_codes', 'dialing_codes.id', '=', 'phone_numbers.dialing_code_id')
            ->with('individualProfileImage')
            ->select(
                'users.*',
                'addresses.city',
                'dialing_codes.dialing_code',
                'phone_numbers.phone_number'
            )
            ->get();

        if ($results->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        // Append full image URL to each user
        $results->each(function ($user) {
            if ($user->individualProfileImage) {
                $user->image_url = $user->individualProfileImage->image_path
                    ? url(Storage::url($user->individualProfileImage->image_path))
                    : null;
            }
        });

        return response()->json([
            'status' => true,
            'data' => $results
        ]);
    }


    // Check if the individual is already a member of the organization, used in create function on member folder
    public function checkMember(Request $request)
    {
        $validated = $request->validate([
            'org_type_user_id' => 'required|exists:users,id',
            'individual_type_user_id' => 'required|exists:users,id',
        ]);

        // Check if the individual is already in the org_members list
        $exists = DB::table('org_members')
            ->where('org_type_user_id', $request->org_type_user_id)
            ->where('individual_type_user_id', $request->individual_type_user_id)
            ->exists();

        return response()->json([
            'status' => true,
            'data' => ['exists' => $exists]
        ]);
    }

    public function create() {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'org_type_user_id' => 'required|exists:users,id',
            'individual_type_user_id' => 'required|exists:users,id',
        ]);
        $orgMember = OrgMember::create([
            'org_type_user_id' => $validated['org_type_user_id'],
            'individual_type_user_id' => $validated['individual_type_user_id'],
            'is_active' => true,
        ]);
        $individualUser = User::find($validated['individual_type_user_id']);
        $orgUser = User::find($validated['org_type_user_id']);
        $orgName = $orgUser ? $orgUser->org_name : 'The Organization';
        if ($individualUser) {
            Mail::to($individualUser->email)->queue(new AddMemberSuccessMail($individualUser->first_name, $orgName));
        }
        // User::find($individualUser->id)->notify(new AddMemberSuccess($orgName));
        // Send DB notification immediately (no queue)
        $individualUser->notify(new AddMemberSuccess(
            orgName: $orgUser->org_name ?? 'The Organization',
            // actorName: auth()->user()->name ?? 'System',
            // actorId: auth()->id()
        ));
        return response()->json([
            'status' => true,
            'message' => 'Member added successfully',
            'data'    => ['id' => $orgMember->id], // ⬅️ include id
        ]);
    }
    public function show($id)
    {
        $userId = Auth::id();

        $member = OrgMember::with(['individual.phoneNumber', 'membershipStatus', 'membershipType', 'memberProfileImage'])
            ->where('org_type_user_id', $userId)
            ->where('id', $id)
            ->first();

        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Member not found'], 404);
        }

        $member->image_url = ($member->memberProfileImage && $member->memberProfileImage->image_path)
            ? url(Storage::url($member->memberProfileImage->image_path))
            : null;
        unset($member->memberProfileImage);

        return response()->json([
            'status' => true,
            'data'   => $member,
        ]);
    }


    public function edit(OrgMember $orgMember) {}

    public function update(Request $request, $id)
    {
        try {
            $member = OrgMember::findOrFail($id);

            // Validate the incoming request data
            $request->validate([
                'existing_membership_id' => 'nullable|string|max:255',
                'membership_type_id' => 'nullable|numeric|exists:membership_types,id',
                'membership_start_date' => 'nullable|date',
                'membership_status_id' => 'nullable|exists:membership_statuses,id',
                'approved_by' => 'nullable|exists:users,id',
                'approved_at' => 'nullable|date',
                'membership_source' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'sponsored_user_id' => 'nullable|exists:users,id',
                // 'is_active' => 'boolean',
            ]);
            $member->existing_membership_id = $request->existing_membership_id;
            $member->membership_type_id = $request->membership_type_id;
            $member->membership_start_date = $request->membership_start_date;
            $member->membership_status_id = $request->membership_status_id;
            $member->approved_by = $request->approved_by;
            $member->approved_at = $request->approved_at;
            $member->membership_source = $request->membership_source;
            $member->notes = $request->notes;
            $member->sponsored_user_id = $request->sponsored_user_id;
            // $member->is_active = $request->is_active;

            // dd($member);exit;
            $member->save();

            return response()->json([
                'status' => true,
                'message' => 'Member updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update member.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $member = OrgMember::findOrFail($id);
            $member->delete();
            return response()->json([
                'status' => true,
                'message' => 'Member deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete member.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
