<?php
namespace App\Http\Controllers\Ecommerce\Product;
use App\Http\Controllers\Controller;

use App\Models\ProductMedia;
use Illuminate\Http\Request;

class ProductMediaController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ProductMedia $productMedia) {}
    public function edit(ProductMedia $productMedia) {}
    public function update(Request $request, ProductMedia $productMedia) {}
    public function destroy(ProductMedia $productMedia) {}
}
