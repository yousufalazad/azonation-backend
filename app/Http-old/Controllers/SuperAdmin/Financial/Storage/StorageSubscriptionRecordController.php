<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Storage;
use App\Http\Controllers\Controller;

use App\Models\StorageSubscriptionRecord;
use Illuminate\Http\Request;

class StorageSubscriptionRecordController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(StorageSubscriptionRecord $storageSubscriptionRecord) {}
    public function edit(StorageSubscriptionRecord $storageSubscriptionRecord) {}
    public function update(Request $request, StorageSubscriptionRecord $storageSubscriptionRecord) {}
    public function destroy(StorageSubscriptionRecord $storageSubscriptionRecord) {}
}
