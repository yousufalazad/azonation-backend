<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(TaxRate $taxRate) {}
    public function edit(TaxRate $taxRate) {}
    public function update(Request $request, TaxRate $taxRate) {}
    public function destroy(TaxRate $taxRate) {}
}
