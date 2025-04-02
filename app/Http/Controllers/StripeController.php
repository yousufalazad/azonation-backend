<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class StripeController extends Controller
{
    public function createCheckoutSession(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Initialize Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Create Stripe Checkout Session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($invoice->currency_code),
                    'product_data' => [
                        'name' => 'Invoice #'.$invoice->billing_code,
                        'description' => $invoice->description,
                    ],
                    'unit_amount' => $invoice->balance_due * 100, // Stripe expects amount in cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('stripe.success', ['invoiceId' => $invoice->id]),
            'cancel_url' => route('stripe.cancel', ['invoiceId' => $invoice->id]),
        ]);

        return response()->json(['url' => $session->url]);
    }

    public function success(Request $request, $invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        
        // Update payment and invoice records
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $invoice->user_id,
            'user_name' => $invoice->user_name,
            'payment_amount' => $invoice->balance_due,
            'payment_method' => 'card',
            'payment_date' => Carbon::now(),
            'payment_gateway' => 'Stripe',
            'transaction_id' => $request->query('session_id'),
            'payment_status' => 'completed',
            'payment_currency' => $invoice->currency_code,
            'amount_paid' => $invoice->balance_due,
            'total_due' => 0,
            'currency_code' => $invoice->currency_code,
            'transaction_status' => 'successful',
            'paid_at' => Carbon::now(),
        ]);

        // Update invoice status
        $invoice->update([
            'payment_status' => 'paid',
            'amount_paid' => $invoice->total_amount,
            'balance_due' => 0,
            'invoice_status' => 'closed'
        ]);

        return redirect()->route('dashboard')->with('success', 'Payment successful');
    }

    public function cancel($invoiceId)
    {
        return redirect()->route('dashboard')->with('error', 'Payment was cancelled');
    }
}
