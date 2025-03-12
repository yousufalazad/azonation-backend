<?php
namespace App\Http\Controllers\Org\Membership;
use App\Http\Controllers\Controller;

use App\Models\OrgMembershipRecord;
use Illuminate\Http\Request;

class OrgMembershipRecordController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(OrgMembershipRecord $orgMembershipRecord) {}
    public function edit(OrgMembershipRecord $orgMembershipRecord) {}
    public function update(Request $request, OrgMembershipRecord $orgMembershipRecord) {}
    public function destroy(OrgMembershipRecord $orgMembershipRecord) {}
}
