<?php
namespace App\Http\Controllers\Org\Membership;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Controllers\Controller;

use App\Models\OrgMembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class OrgMembershipTypeController extends Controller
{
    use ResolvesCurrentOrg;

   
    public function index()
    {
        $userId = $this->orgIdOrFail();
        $orgMembershipTypes = OrgMembershipType::where('org_type_user_id', $userId)->with('membershipType')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership types retrieved successfully.',
            'data' => $orgMembershipTypes
        ]);
    }

    public function store(Request $request)
    {
        // The organisation always comes from the session, never from the form
        $request->merge(['org_type_user_id' => $this->orgIdOrFail()]);

        $validator = Validator::make($request->all(), [
            'membership_type_id' => 'required|exists:membership_types,id',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
            'meta' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }
            $request['org_type_user_id'] = $this->orgIdOrFail();

        $orgMembershipType = OrgMembershipType::create($request->only([
            'org_type_user_id',
            'membership_type_id',
            'starts_on',
            'ends_on',
            'is_active',
            'is_public',
            'sort_order',
            'meta'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership type created successfully.',
            'data' => $orgMembershipType
        ]);
    }

    
    public function show($id)
    {
        $orgMembershipType = $this->owned(OrgMembershipType::class, 'org_type_user_id')->with( 'membershipType')->find($id);

        if (!$orgMembershipType) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership type not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership type retrieved successfully.',
            'data' => $orgMembershipType
        ]);
    }

    public function update(Request $request, $id)
    {
        // The organisation always comes from the session, never from the form
        $request->merge(['org_type_user_id' => $this->orgIdOrFail()]);

        $orgMembershipType = $this->owned(OrgMembershipType::class, 'org_type_user_id')->find($id);

        if (!$orgMembershipType) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership type not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'membership_type_id' => 'required|exists:membership_types,id',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
            'meta' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred. Please try again.',
                'errors' => $validator->errors()
            ], 422);
        }

        $orgMembershipType->update($request->only([
            'membership_type_id',
            'starts_on',
            'ends_on',
            'is_active',
            'is_public',
            'sort_order',
            'meta'
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership type updated successfully.',
            'data' => $orgMembershipType
        ]);
    }

   
    public function destroy($id)
    {
        $orgMembershipType = $this->owned(OrgMembershipType::class, 'org_type_user_id')->find($id);

        if (!$orgMembershipType) {
            return response()->json([
                'status' => false,
                'message' => 'Organisation membership type not found.'
            ], 404);
        }

        $orgMembershipType->delete();

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership type deleted successfully.'
        ]);
    }
}