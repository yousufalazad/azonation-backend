<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Management;
use App\Http\Controllers\Controller;

use App\Models\ManagementSubscription;
use Illuminate\Http\Request;

class ManagementSubscriptionController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ManagementSubscription $managementSubscription) {}
    public function edit(ManagementSubscription $managementSubscription) {}
    public function update(Request $request, ManagementSubscription $managementSubscription) {}
    public function destroy(ManagementSubscription $managementSubscription) {}
}
