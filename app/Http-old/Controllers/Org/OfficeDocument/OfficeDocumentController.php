<?php
namespace App\Http\Controllers\Org\OfficeDocument;
use App\Http\Controllers\Controller;

use App\Models\OfficeDocument;
use Illuminate\Http\Request;

class OfficeDocumentController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(OfficeDocument $officeDocument) {}
    public function edit(OfficeDocument $officeDocument) {}
    public function update(Request $request, OfficeDocument $officeDocument) {}
    public function destroy(OfficeDocument $officeDocument) {}
}
