<?php
namespace App\Http\Controllers\Ecommerce\Product;
use App\Http\Controllers\Controller;

use App\Models\ProductAttribute;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ProductAttribute $productAttribute) {}
    public function edit(ProductAttribute $productAttribute) {}
    public function update(Request $request, ProductAttribute $productAttribute) {}
    public function destroy(ProductAttribute $productAttribute) {}
}
