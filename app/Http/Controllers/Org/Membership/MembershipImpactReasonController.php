<?php
namespace App\Http\Controllers\Org\Membership;
use App\Http\Controllers\Controller;

use App\Models\MembershipImpactReason;
use Illuminate\Http\Request;

class MembershipImpactReasonController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(MembershipImpactReason $membershipImpactReason) {}
    public function edit(MembershipImpactReason $membershipImpactReason) {}
    public function update(Request $request, MembershipImpactReason $membershipImpactReason) {}
    public function destroy(MembershipImpactReason $membershipImpactReason) {}
}
