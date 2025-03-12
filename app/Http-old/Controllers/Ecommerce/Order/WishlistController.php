<?php
namespace App\Http\Controllers\Ecommerce\Order;
use App\Http\Controllers\Controller;

use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(Wishlist $wishlist) {}
    public function edit(Wishlist $wishlist) {}
    public function update(Request $request, Wishlist $wishlist) {}
    public function destroy(Wishlist $wishlist) {}
}
