<?php
namespace App\Http\Controllers\Ecommerce\Product;
use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(ProductReview $productReview) {}
    public function edit(ProductReview $productReview) {}
    public function update(Request $request, ProductReview $productReview) {}
    public function destroy(ProductReview $productReview) {}
}
