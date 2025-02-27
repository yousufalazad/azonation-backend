<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Management;
use App\Http\Controllers\Controller;

use App\Models\ManagementSubscriptionRecord;
use Illuminate\Http\Request;

class ManagementSubscriptionRecordController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ManagementSubscriptionRecord $managementSubscriptionRecord) {}
    public function edit(ManagementSubscriptionRecord $managementSubscriptionRecord) {}
    public function update(Request $request, ManagementSubscriptionRecord $managementSubscriptionRecord) {}
    public function destroy(ManagementSubscriptionRecord $managementSubscriptionRecord) {}
}
