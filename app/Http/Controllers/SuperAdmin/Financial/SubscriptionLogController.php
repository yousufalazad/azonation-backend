<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;
use App\Models\SubscriptionLog;
use Illuminate\Http\Request;

class SubscriptionLogController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(SubscriptionLog $subscriptionLog) {}
    public function edit(SubscriptionLog $subscriptionLog) {}
    public function update(Request $request, SubscriptionLog $subscriptionLog) {}
    public function destroy(SubscriptionLog $subscriptionLog) {}
}
