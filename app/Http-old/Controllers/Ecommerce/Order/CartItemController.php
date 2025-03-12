<?php
namespace App\Http\Controllers\Ecommerce\Order;
use App\Http\Controllers\Controller;

use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(CartItem $cartItem) {}
    public function edit(CartItem $cartItem) {}
    public function update(Request $request, CartItem $cartItem) {}
    public function destroy(CartItem $cartItem) {}
}
