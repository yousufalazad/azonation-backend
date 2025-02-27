<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Storage;
use App\Http\Controllers\Controller;

use App\Models\StoragePricing;
use Illuminate\Http\Request;

class StoragePricingController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(StoragePricing $storagePricing) {}
    public function edit(StoragePricing $storagePricing) {}
    public function update(Request $request, StoragePricing $storagePricing) {}
    public function destroy(StoragePricing $storagePricing) {}
}
