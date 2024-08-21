<?php

namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\OrgMemberList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Notifications\MemberAddSuccessful;
use App\Models\User;
use App\Models\Individual;
use App\Models\Organisation;

class OrgMemberListController extends Controller
{

    public function totalOrgMemberCount($orgId)
    {
        //$orgTotalMemberCount = OrgMemberList::select('db_table_name.*')->where('org_id', $orgId)->get();
        //$orgTotalMemberCount = OrgMemberList::select('id','name', '--')->where('org_id', $orgId)->get();

        $totalOrgMemberCount = OrgMemberList::where('org_id', $orgId)->count();

        return response()->json([
            'status' => true,
            'totalOrgMemberCount' => $totalOrgMemberCount
        ]);
    }
    public function getMembersByOrgId($orgId)
    {

        $members = OrgMemberList::where('org_id', $orgId)
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

        $results = Individual::where('id', 'like', "%{$query}%")
            ->orWhere('user_id', 'like', "%{$query}%")
            ->orWhere('azon_id', 'like', "%{$query}%")
            ->orWhere('full_name', 'like', "%{$query}%")
            ->get();

        return response()->json([
            'status' => true,
            'data' => $results
        ]);
    }

    public function addMember(Request $request)
    {
        $validated = $request->validate([
            // 'org_id' => 'required',
            // 'individual_id' => 'required',
            'org_id' => 'required|exists:organisations,id',
            'individual_id' => 'required|exists:individuals,id',
        ]);

        $OrgMemberList = OrgMemberList::create([
            'org_id' => $validated['org_id'],
            'individual_id' => $validated['individual_id'],
            'status' => 1
        ]);
        
        User::find(Auth::user()->id)->notify(new MemberAddSuccessful($OrgMemberList));
        

        return response()->json([
            'status' => true,
            'message' => 'Member added successfully'
        ]);

        
        // $getUserId = Organisation::where('org_id', $request->org_id)->get();
        // $getUserIdfromUserTable = User::where('id', $getUserId->user_id);
        // User::find($getUserIdfromUserTable->id)->notify(new MemberAddSuccessful($OrgMemberList->individual_id));
        

        //        User::find(Auth::user()->id)->notify(new MemberAddSuccessful($OrgMemberList->individual_id));
    }

    //Notification for add member, member mark as read
    public function markAsRead(){
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    public function index()
    {
        //
    }


    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $request->validate([
        //     'org_id' => 'required|integer',
        //     'org_id' => 'required|integer',
        // ]);

        // Create a new individual record associated with the user


        OrgMemberList::create([
            'org_id' => $request->org_id,
            'individual_id' => $request->individual_id,
            //'status' => $request->status,

        ]);

        // Return a success response
        //return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

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
