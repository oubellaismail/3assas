<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

class StripeController extends Controller
{
    public function index()
    {
        return view('stripe.form');
    }

    public function checkout(Request $request)
    {
        // Validate request data
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string'
        ]);

        // Format the amount to cents (Stripe requires amounts in cents)
        $amount = (int)($request->amount * 100);

        // Set your Stripe secret key
        Stripe::setApiKey(config('stripe.sk_key'));

        try {
            // Create a checkout session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => config('stripe.currency'),
                            'product_data' => [
                                'name' => 'Payment for ' . $request->description,
                            ],
                            'unit_amount' => $amount,
                        ],
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'payment',
                'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel'),
            ]);

            // Redirect to Stripe Checkout
            return redirect($session->url);

        } catch (ApiErrorException $e) {
            return redirect()->route('payment.error')
                ->with('error', $e->getMessage());
        }
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('payment.error')
                ->with('error', 'No session ID provided.');
        }

        try {
            // Set your Stripe secret key
            Stripe::setApiKey(config('stripe.sk_key'));
            
            // Retrieve the session to get payment details
            $session = Session::retrieve($sessionId);
            
            // Get payment intent ID
            $paymentIntentId = $session->payment_intent;
            
            // Here you would typically:
            // 1. Verify the payment status
            // 2. Update your database records
            // 3. Fulfill the order or provide access to purchased content
            
            return view('stripe.success', [
                'paymentIntent' => $paymentIntentId
            ]);
            
        } catch (ApiErrorException $e) {
            return redirect()->route('payment.error')
                ->with('error', $e->getMessage());
        }
    }

    public function cancel()
    {
        return view('stripe.cancel');
    }
}
