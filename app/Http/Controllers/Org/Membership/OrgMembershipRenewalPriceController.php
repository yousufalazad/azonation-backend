<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Routing\Controller;
use App\Models\OrgMembershipRenewalCycle;
use App\Models\OrgMembershipRenewalPrice;
use App\Models\OrgMembershipType;
use Illuminate\Http\Request;

/**
 * Renewal fees: how much a membership type costs for one renewal period.
 * Amounts are stored in minor units (100 = 1.00).
 */
class OrgMembershipRenewalPriceController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:org-membership-renewal-price.read')->only(['index', 'show']);
        $this->middleware('org.permission:org-membership-renewal-price.create')->only(['store']);
        $this->middleware('org.permission:org-membership-renewal-price.update')->only(['update']);
        $this->middleware('org.permission:org-membership-renewal-price.delete')->only(['destroy']);
    }

    public function index()
    {
        $prices = $this->owned(OrgMembershipRenewalPrice::class, 'org_type_user_id')
            ->with(['orgMembershipType.membershipType:id,name', 'orgMembershipRenewalCycle.memberRenewalCycle:id,name,duration_in_months'])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json(['status' => true, 'data' => $prices]);
    }

    public function store(Request $request)
    {
        $orgId = $this->orgIdOrFail();
        $data = $this->validated($request);

        $price = OrgMembershipRenewalPrice::create($data + ['org_type_user_id' => $orgId]);

        return response()->json(['status' => true, 'data' => $price]);
    }

    public function show($id)
    {
        $price = $this->owned(OrgMembershipRenewalPrice::class, 'org_type_user_id')
            ->with(['orgMembershipType.membershipType:id,name', 'orgMembershipRenewalCycle.memberRenewalCycle'])
            ->findOrFail($id);

        return response()->json(['status' => true, 'data' => $price]);
    }

    public function update(Request $request, $id)
    {
        $price = $this->owned(OrgMembershipRenewalPrice::class, 'org_type_user_id')->findOrFail($id);
        $price->update($this->validated($request));

        return response()->json(['status' => true, 'data' => $price]);
    }

    public function destroy($id)
    {
        $this->owned(OrgMembershipRenewalPrice::class, 'org_type_user_id')->findOrFail($id)->delete();

        return response()->json(['status' => true]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'org_membership_type_id' => 'required|integer',
            'org_mem_renewal_cycle_id' => 'required|integer',
            'currency' => 'required|string|size:3',
            'unit_amount_minor' => 'required|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'org_notes' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Both must be this organisation's own membership type and renewal period
        $this->ensureOwnedParent(OrgMembershipType::class, $data['org_membership_type_id'], 'org_type_user_id');
        $this->ensureOwnedParent(OrgMembershipRenewalCycle::class, $data['org_mem_renewal_cycle_id'], 'org_type_user_id');

        $data['currency'] = strtoupper($data['currency']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_recurring'] = true;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
