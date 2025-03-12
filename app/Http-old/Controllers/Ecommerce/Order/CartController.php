<?php
namespace App\Http\Controllers\Ecommerce\Order;
use App\Http\Controllers\Controller;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(Cart $cart) {}
    public function edit(Cart $cart) {}
    public function update(Request $request, Cart $cart) {}
    public function destroy(Cart $cart) {}
}
