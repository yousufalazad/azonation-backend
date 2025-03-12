<?php
namespace App\Http\Controllers\SuperAdmin\Financial\Management;
use App\Http\Controllers\Controller;

use App\Models\ManagementPricing;
use Illuminate\Http\Request;

class ManagementPricingController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ManagementPricing $managementPricing) {}
    public function edit(ManagementPricing $managementPricing) {}
    public function update(Request $request, ManagementPricing $managementPricing) {}
    public function destroy(ManagementPricing $managementPricing) {}
}
