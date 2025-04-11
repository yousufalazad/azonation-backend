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

class OrgMemberController extends Controller
{
    use Notifiable;
    public function getOrgAllMemberName(Request $request)
    {
        $userId = Auth::id();
        $getOrgAllMemberName = OrgMember::with(['individual:id,name', 'membershipType',])
            ->where('org_type_user_id', $userId)
            ->where('is_active', '1')
            ->get();
        return response()->json([
            'status' => true,
            'data' => $getOrgAllMemberName
        ]);
    }
    // public function getOrgAllMembers(Request $request)
    // {
    //     $userId = Auth::id();
    //     // $userId = $request->user()->id;

    //     // $getOrgAllMembers = OrgMember::with(['individual', 'membershipType', 'memberProfileImage'])
    //     $getOrgAllMembers = OrgMember::with(['individual', 'membershipType', 'memberProfileImage'])
    //         ->where('org_type_user_id', $userId)
    //         ->where('is_active', '1')
    //         ->get();
    //     return response()->json([
    //         'status' => true,
    //         'data' => $getOrgAllMembers
    //     ]);
    // }

    public function getOrgAllMembers(Request $request)
    {
        $userId = Auth::id();
        $getOrgAllMembers = OrgMember::with(['individual:id,name', 'membershipType', 'memberProfileImage'])
            ->where('org_type_user_id', $userId)
            ->where('is_active', '1')
            ->get();
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

    public function search(Request $request)
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
    public function addMember(Request $request)
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
        $orgName = $orgUser ? $orgUser->name : 'The Organization';
        if ($individualUser) {
            Mail::to($individualUser->email)->queue(new AddMemberSuccessMail($individualUser->name, $orgName));
        }
        User::find($individualUser->id)->notify(new AddMemberSuccess($orgName));
        return response()->json([
            'status' => true,
            'message' => 'Member added successfully',
        ]);
    }

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
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(OrgMember $orgMember) {}
    public function edit(OrgMember $orgMember) {}
    public function update(Request $request, $id)
    {
        try {
            $member = OrgMember::findOrFail($id);
    
            // Validate the incoming request data
            $request->validate([
                'individual_name' => 'required|string|max:255',
                'existing_membership_id' => 'required|string|max:255',
                'membership_type' => 'required|string|max:255',
                'membership_start_date' => 'required|date',
                'sponsored_user_id' => 'nullable|exists:users,id', // Assuming it's a user ID
                'is_active' => 'required|boolean',
            ]);
    
            // Update the member data
            // $member->individual->name = $request->individual_name;
            $member->existing_membership_id = $request->existing_membership_id;
            $member->membership_type->name = $request->membership_type;
            $member->membership_start_date = $request->membership_start_date;
            $member->sponsored_user_id = $request->sponsored_user_id;
            $member->is_active = $request->is_active;
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
    
    
    public function destroy($id) {
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
