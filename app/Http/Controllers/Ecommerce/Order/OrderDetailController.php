<?php
namespace App\Http\Controllers\Ecommerce\Order;
use App\Http\Controllers\Controller;

use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderDetailController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(OrderDetail $orderDetail) {}
    public function edit(OrderDetail $orderDetail) {}
    public function update(Request $request, OrderDetail $orderDetail) {}
    public function destroy(OrderDetail $orderDetail) {}
}
