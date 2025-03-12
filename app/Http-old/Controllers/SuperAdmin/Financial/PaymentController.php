<?php
namespace App\Http\Controllers\SuperAdmin\Financial;
use App\Http\Controllers\Controller;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show(Payment $payment) {}
    public function edit(Payment $payment) {}
    public function update(Request $request, Payment $payment) {}
    public function destroy(Payment $payment) {}
}
