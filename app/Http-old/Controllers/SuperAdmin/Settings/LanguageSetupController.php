<?php
namespace App\Http\Controllers\SuperAdmin\Settings;
use App\Http\Controllers\Controller;

use App\Models\LanguageSetup;
use Illuminate\Http\Request;

class LanguageSetupController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(LanguageSetup $languageSetup) {}
    public function edit(LanguageSetup $languageSetup) {}
    public function update(Request $request, LanguageSetup $languageSetup) {}
    public function destroy(LanguageSetup $languageSetup) {}
}
