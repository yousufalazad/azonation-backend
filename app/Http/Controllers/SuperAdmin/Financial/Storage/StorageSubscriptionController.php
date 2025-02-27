<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Storage;
use App\Http\Controllers\Controller;

use App\Models\StorageSubscription;
use Illuminate\Http\Request;

class StorageSubscriptionController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(StorageSubscription $storageSubscription) {}
    public function edit(StorageSubscription $storageSubscription) {}
    public function update(Request $request, StorageSubscription $storageSubscription) {}
    public function destroy(StorageSubscription $storageSubscription) {}
}
