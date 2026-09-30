<?php
namespace App\Http\Controllers\Org\Membership;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Models\OrgMembershipType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * The membership types an organisation offers, chosen from the platform list
 * (e.g. General member, Lifetime member).
 */
class OrgMembershipTypeController extends Controller
{
    use ResolvesCurrentOrg;

    private const FIELDS = ['membership_type_id', 'starts_on', 'ends_on', 'is_active', 'is_public', 'sort_order', 'meta'];

    public function __construct()
    {
        $this->middleware('org.permission:org-membership-type.read')->only(['index', 'show']);
        $this->middleware('org.permission:org-membership-type.create')->only(['store']);
        $this->middleware('org.permission:org-membership-type.update')->only(['update']);
        $this->middleware('org.permission:org-membership-type.delete')->only(['destroy']);
    }

    public function index()
    {
        $orgId = $this->orgIdOrFail();
        $types = OrgMembershipType::where('org_type_user_id', $orgId)->with('membershipType')
            ->orderBy('sort_order')
            ->get();

        // How many active members have each type, and how many renewal fees use it
        $members = DB::table('org_members')->where('org_type_user_id', $orgId)->where('is_active', 1)
            ->select('membership_type_id', DB::raw('count(*) as n'))->groupBy('membership_type_id')->pluck('n', 'membership_type_id');
        $fees = DB::table('org_membership_renewal_prices')->where('org_type_user_id', $orgId)
            ->select('org_membership_type_id', DB::raw('count(*) as n'))->groupBy('org_membership_type_id')->pluck('n', 'org_membership_type_id');
        $types->each(function ($t) use ($members, $fees) {
            $t->members_count = (int) ($members[$t->membership_type_id] ?? 0);
            $t->fees_count = (int) ($fees[$t->id] ?? 0);
        });

        return response()->json([
            'status' => true,
            'message' => 'Organisation membership types retrieved successfully.',
            'data' => $types,
        ]);
    }

    public function store(Request $request)
    {
        $orgId = $this->orgIdOrFail();
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.', 'errors' => $validator->errors()], 422);
        }

        // Offering the same type twice does nothing
        $existing = OrgMembershipType::where('org_type_user_id', $orgId)->where('membership_type_id', $request->membership_type_id)->first();
        if ($existing) {
            return response()->json(['status' => true, 'message' => 'Already offered.', 'data' => $existing]);
        }

        $type = OrgMembershipType::create($request->only(self::FIELDS) + ['org_type_user_id' => $orgId]);

        return response()->json(['status' => true, 'message' => 'Organisation membership type created successfully.', 'data' => $type]);
    }

    public function show($id)
    {
        $type = $this->owned(OrgMembershipType::class, 'org_type_user_id')->with('membershipType')->find($id);
        if (!$type) {
            return response()->json(['status' => false, 'message' => 'Organisation membership type not found.'], 404);
        }

        return response()->json(['status' => true, 'message' => 'Organisation membership type retrieved successfully.', 'data' => $type]);
    }

    public function update(Request $request, $id)
    {
        $type = $this->owned(OrgMembershipType::class, 'org_type_user_id')->find($id);
        if (!$type) {
            return response()->json(['status' => false, 'message' => 'Organisation membership type not found.'], 404);
        }
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.', 'errors' => $validator->errors()], 422);
        }
        $type->update($request->only(self::FIELDS));

        return response()->json(['status' => true, 'message' => 'Organisation membership type updated successfully.', 'data' => $type]);
    }

    public function destroy($id)
    {
        $type = $this->owned(OrgMembershipType::class, 'org_type_user_id')->find($id);
        if (!$type) {
            return response()->json(['status' => false, 'message' => 'Organisation membership type not found.'], 404);
        }
        // Fees point at the type; take them away first so none are left without one
        if (DB::table('org_membership_renewal_prices')->where('org_membership_type_id', $type->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Remove the renewal fees for this membership type first.'], 422);
        }
        $type->delete();

        return response()->json(['status' => true, 'message' => 'Organisation membership type deleted successfully.']);
    }

    private function rules(): array
    {
        return [
            'membership_type_id' => 'required|exists:membership_types,id',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'sort_order' => 'integer',
            'meta' => 'nullable|json',
        ];
    }
}
