<?php
namespace App\Http\Controllers\Ecommerce;
use App\Http\Controllers\Controller;

use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(Attribute $attribute) {}
    public function edit(Attribute $attribute) {}
    public function update(Request $request, Attribute $attribute) {}
    public function destroy(Attribute $attribute) {}
}
