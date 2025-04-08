<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaypalController extends Controller
{
    public function createPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string'
        ]);
        
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.success'),
                "cancel_url" => route('paypal.cancel'),
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->amount
                    ],
                    "description" => $request->description
                ]
            ]
        ]);
        
        if (isset($response['id']) && $response['id'] != null) {
            // For PayPal checkout, save order ID in session
            session(['paypal_order_id' => $response['id']]);
            
            // Redirect to PayPal
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        }
        
        return redirect()->route('payment.error')
            ->with('error', 'Something went wrong with PayPal: ' . json_encode($response));
    }
    
    public function paymentSuccess(Request $request)
    {
        $orderId = session('paypal_order_id');
        
        if (empty($orderId)) {
            return redirect()->route('payment.error')
                ->with('error', 'Order ID not found in session');
        }
        
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        
        // Capture the order
        $response = $provider->capturePaymentOrder($orderId);
        
        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            // Payment successful, clear session
                ()->forget('paypal_order_id');
            
            // Get transaction details
            $captureId = $response['purchase_units'][0]['payments']['captures'][0]['id'];
            
            return redirect()->route('payment.success')
                ->with('success', 'Payment completed successfully! Transaction ID: ' . $captureId);
        }
        
        return redirect()->route('payment.error')
            ->with('error', 'Payment failed: ' . json_encode($response));
    }
    
    public function paymentCancel(Request $request)
    {
        // Clear session
        session()->forget('paypal_order_id');
        
        return redirect()->route('payment.cancel')
            ->with('error', 'Payment was cancelled.');
    }
}