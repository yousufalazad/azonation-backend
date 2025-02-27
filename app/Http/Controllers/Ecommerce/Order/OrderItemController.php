<?php
namespace App\Http\Controllers\Ecommerce\Order;
use App\Http\Controllers\Controller;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(OrderItem $orderItem) {}
    public function edit(OrderItem $orderItem) {}
    public function update(Request $request, OrderItem $orderItem) {}
    public function destroy(OrderItem $orderItem) {}
}
