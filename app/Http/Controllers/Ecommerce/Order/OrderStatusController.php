<?php
namespace App\Http\Controllers\Ecommerce\Order;
use App\Http\Controllers\Controller;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(OrderStatus $orderStatus) {}
    public function edit(OrderStatus $orderStatus) {}
    public function update(Request $request, OrderStatus $orderStatus) {}
    public function destroy(OrderStatus $orderStatus) {}
}
