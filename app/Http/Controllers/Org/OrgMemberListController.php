<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Mail\AddMemberSuccessMail;
use Illuminate\Support\Facades\Mail;

use App\Models\OrgMemberList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Notifications\MemberAddSuccessful;
use App\Models\User;
use App\Models\Individual;
use App\Models\Organisation;
use App\Notifications\AddMemberSuccess;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class OrgMemberListController extends Controller
{
    use Notifiable;

    public function totalOrgMemberCount($userId)
    {
        //$orgTotalMemberCount = OrgMemberList::select('db_table_name.*')->where('org_id', $orgId)->get();
        //$orgTotalMemberCount = OrgMemberList::select('id','name', '--')->where('org_id', $orgId)->get();

        $totalOrgMemberCount = OrgMemberList::where('org_type_user_id', $userId)->count();

        return response()->json([
            'status' => true,
            'totalOrgMemberCount' => $totalOrgMemberCount
        ]);
    }
    public function getMemberList($userId)
    {

        $members = OrgMemberList::where('org_type_user_id', $userId)
            ->with('individual')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $members
        ]);
    }

    public function search(Request $request)
{
    $query = $request->input('query');

    $results = User::where('type', 'individual') // Filter by user type
        ->where(function ($q) use ($query) { // Group search conditions
            $q->where('azon_id', 'like', "%{$query}%")
                ->orWhere('name', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhereRaw("CONCAT(dialing_codes.dialing_code, phone_numbers.phone_number) LIKE ?", ["%{$query}%"]); // Search by full phone number
        })
        ->leftJoin('addresses', 'addresses.user_id', '=', 'users.id') // Left join addresses table
        ->leftJoin('countries', 'countries.id', '=', 'addresses.country_id') // Left join countries table
        ->leftJoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id') // Left join phone_numbers table
        ->leftJoin('dialing_codes', 'dialing_codes.id', '=', 'phone_numbers.dialing_code_id') // Left join dialing_codes table
        ->select(
            'users.*',
            'addresses.city',
            'countries.country_name',
            'dialing_codes.dialing_code',
            'phone_numbers.phone_number'
        )
        ->get();

    return response()->json([
        'status' => true,
        'data' => $results
    ]);
}


    // public function search(Request $request)
    // {
    //     $query = $request->input('query');

    //     $results = User::where('type', 'individual') // Filter by user type
    //         ->where(function ($q) use ($query) { // Group search conditions
    //             $q->where('azon_id', 'like', "%{$query}%")
    //                 ->orWhere('name', 'like', "%{$query}%")
    //                 ->orWhere('email', 'like', "%{$query}%");
    //         })
    //         ->leftJoin('addresses', 'addresses.user_id', '=', 'users.id') // Left join addresses table
    //         ->leftJoin('countries', 'countries.id', '=', 'addresses.country_id') // Left join countries table
    //         ->leftJoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id') // Left join phone_numbers table
    //         ->leftJoin('dialing_codes', 'dialing_codes.id', '=', 'phone_numbers.dialing_code_id') // Left join dialing_codes table
    //         ->select(
    //             'users.*',
    //             'addresses.city',
    //             'countries.country_name',
    //             'dialing_codes.dialing_code',
    //             'phone_numbers.phone_number'
    //         )
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $results
    //     ]);
    // }

    public function addMember(Request $request)
    {
        $validated = $request->validate([
            'org_type_user_id' => 'required|exists:users,id',
            'individual_type_user_id' => 'required|exists:users,id',
        ]);

        $OrgMemberList = OrgMemberList::create([
            'org_type_user_id' => $validated['org_type_user_id'],
            'individual_type_user_id' => $validated['individual_type_user_id'],
            'status' => 1
        ]);

        // Retrieve the individual user and the organization name
        $individualUser = User::find($validated['individual_type_user_id']);
        $orgUser = User::find($validated['org_type_user_id']);
        $orgName = $orgUser ? $orgUser->name : 'The Organization'; // Adjust according to your org naming conventions

        if ($individualUser) {
            // Send the email to the individual user
            Mail::to($individualUser->email)->send(new AddMemberSuccessMail($individualUser->name, $orgName));
        }

        User::find($individualUser->id)->notify(new AddMemberSuccess($orgName));


        return response()->json([
            'status' => true,
            'message' => 'Member added successfully',
        ]);
    }



    public function index() {}


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Display the specified resource.
     */
    public function show(OrgMemberList $orgMemberList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OrgMemberList $orgMemberList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrgMemberList $orgMemberList)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrgMemberList $orgMemberList)
    {
        //
    }
}
