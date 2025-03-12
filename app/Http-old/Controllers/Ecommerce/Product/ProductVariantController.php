<?php
namespace App\Http\Controllers\Ecommerce\Product;
use App\Http\Controllers\Controller;

use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ProductVariant $productVariant) {}
    public function edit(ProductVariant $productVariant) {}
    public function update(Request $request, ProductVariant $productVariant) {}
    public function destroy(ProductVariant $productVariant) {}
}
