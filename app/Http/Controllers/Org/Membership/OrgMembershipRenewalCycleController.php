<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Routing\Controller;
use App\Models\OrgMembershipRenewalCycle;
use App\Models\OrgMembershipRenewalPrice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The renewal periods an organisation offers (e.g. yearly, every two years),
 * and when they fall due: on each member's joining date, or on one fixed date.
 */
class OrgMembershipRenewalCycleController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:org-membership-renewal-cycle.read')->only(['index', 'show']);
        $this->middleware('org.permission:org-membership-renewal-cycle.create')->only(['store']);
        $this->middleware('org.permission:org-membership-renewal-cycle.update')->only(['update']);
        $this->middleware('org.permission:org-membership-renewal-cycle.delete')->only(['destroy']);
    }

    public function index()
    {
        $cycles = $this->owned(OrgMembershipRenewalCycle::class, 'org_type_user_id')
            ->with('memberRenewalCycle:id,name,duration_in_months')
            ->orderBy('created_at')
            ->get();

        return response()->json(['status' => true, 'data' => $cycles]);
    }

    public function store(Request $request)
    {
        $orgId = $this->orgIdOrFail();
        $data = $this->validated($request);

        $exists = OrgMembershipRenewalCycle::where('org_type_user_id', $orgId)
            ->where('member_renewal_cycle_id', $data['member_renewal_cycle_id'])->exists();
        if ($exists) {
            return response()->json(['status' => false, 'message' => 'You already offer this renewal period.'], 422);
        }

        $cycle = OrgMembershipRenewalCycle::create($data + ['org_type_user_id' => $orgId]);

        return response()->json(['status' => true, 'data' => $cycle->load('memberRenewalCycle:id,name,duration_in_months')]);
    }

    public function show($id)
    {
        $cycle = $this->owned(OrgMembershipRenewalCycle::class, 'org_type_user_id')->with('memberRenewalCycle')->findOrFail($id);

        return response()->json(['status' => true, 'data' => $cycle]);
    }

    public function update(Request $request, $id)
    {
        $cycle = $this->owned(OrgMembershipRenewalCycle::class, 'org_type_user_id')->findOrFail($id);
        $data = $this->validated($request);

        $clash = OrgMembershipRenewalCycle::where('org_type_user_id', $cycle->org_type_user_id)
            ->where('member_renewal_cycle_id', $data['member_renewal_cycle_id'])
            ->where('id', '!=', $cycle->id)->exists();
        if ($clash) {
            return response()->json(['status' => false, 'message' => 'You already offer this renewal period.'], 422);
        }

        $cycle->update($data);

        return response()->json(['status' => true, 'data' => $cycle->load('memberRenewalCycle:id,name,duration_in_months')]);
    }

    public function destroy($id)
    {
        $cycle = $this->owned(OrgMembershipRenewalCycle::class, 'org_type_user_id')->findOrFail($id);

        if (OrgMembershipRenewalPrice::where('org_mem_renewal_cycle_id', $cycle->id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Remove the fees for this renewal period first.'], 422);
        }
        $cycle->delete();

        return response()->json(['status' => true]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'member_renewal_cycle_id' => 'required|exists:membership_renewal_cycles,id',
            'alignment' => ['required', Rule::in(['member_anniversary', 'calendar', 'org_fiscal'])],
            'anchor_month' => 'nullable|integer|between:1,12|required_unless:alignment,member_anniversary',
            'anchor_day' => 'nullable|integer|between:1,31|required_unless:alignment,member_anniversary',
            'grace_days' => 'nullable|integer|between:0,365',
            'is_active' => 'nullable|boolean',
        ]);

        // A fixed date only matters when everyone renews on the same date
        if ($data['alignment'] === 'member_anniversary') {
            $data['anchor_month'] = null;
            $data['anchor_day'] = null;
        }
        $data['grace_days'] = $data['grace_days'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? true;

        return $data;
    }
}
