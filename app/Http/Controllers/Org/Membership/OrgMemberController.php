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
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(OrgMember $orgMember) {}
    public function edit(OrgMember $orgMember) {}
    public function update(Request $request, OrgMember $orgMember) {}
    public function destroy(OrgMember $orgMember) {}
}
