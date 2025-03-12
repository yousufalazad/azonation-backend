<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\CountryCode;
use Illuminate\Http\Request;

class CountryCodeController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(CountryCode $countryCode) {}
    public function edit(CountryCode $countryCode) {}
    public function update(Request $request, CountryCode $countryCode) {}
    public function destroy(CountryCode $countryCode) {}
}
