<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Services\MemberFamilies;
use Illuminate\Routing\Controller;

/**
 * "Member families" in the organisation dashboard: only what current members chose to share.
 */
class MemberFamilySummaryController extends Controller
{
    use ResolvesCurrentOrg;

    public function __construct()
    {
        $this->middleware('org.permission:member-family.read');
    }

    public function index()
    {
        return response()->json(['status' => true, 'data' => MemberFamilies::summary($this->orgIdOrFail())]);
    }
}
