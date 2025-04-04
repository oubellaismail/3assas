# 💸 Laravel PayPal Integration Guide

This guide walks you through setting up PayPal payments in Laravel using the `srmklive/paypal` package.

---

## 📦 Step 1: Install the PayPal Package

```
composer require srmklive/paypal
php artisan vendor:publish --provider="Srmklive\PayPal\Providers\PayPalServiceProvider"
```

---

## 🧪 Step 2: Set Up a PayPal Developer Account

1. Go to [PayPal Developer](https://developer.paypal.com/)
2. Create a sandbox account.
3. Create an app under **My Apps & Credentials**.
4. Retrieve your **Client ID** and **Client Secret**.

---

## ⚙️ Step 3: Configure `.env`

Add the following keys to your `.env` file:

```env
PAYPAL_SANDBOX_CLIENT_ID=your_client_id
PAYPAL_SANDBOX_CLIENT_SECRET=your_client_secret
PAYPAL_MODE=sandbox
```

> Change `sandbox` to `live` when deploying to production.

---

## 🧾 Step 4: Create PayPal Controller

```bash
php artisan make:controller PaypalController
```

Paste the following code inside `app/Http/Controllers/PaypalController.php`:

```php
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
            session()->forget('paypal_order_id');
            
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
```

---

## 🧩 Step 5: Create Views

### 📂 Create Folders

```bash
mkdir -p resources/views/payment
mkdir -p resources/views/layout
```

---

### 📄 Layout File

Create `resources/views/layout/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel PayPal Integration') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 20px; }
        .container { max-width: 960px; }
        .card { margin-top: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .card-header { background-color: #f8f9fa; font-weight: bold; }
        .form-group { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">{{ config('app.name', 'Laravel PayPal') }}</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                        <li class="nav-item"><a class="nav-link" href="{{ route('payment.form') }}">Make Payment</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @yield('content')
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

### 📑 Create Blade Files

#### `resources/views/payment/form.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Payment with PayPal</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('payment.process') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="amount" class="col-md-4 col-form-label text-md-right">Amount ($)</label>
                            <div class="col-md-6">
                                <input id="amount" type="number" class="form-control" name="amount" value="{{ old('amount') }}" required step="0.01" min="1">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="description" class="col-md-4 col-form-label text-md-right">Description</label>
                            <div class="col-md-6">
                                <input id="description" type="text" class="form-control" name="description" value="{{ old('description') }}" required>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Pay with PayPal
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### `resources/views/payment/success.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Payment Successful</div>

                <div class="card-body">
                    <div class="alert alert-success">
                        Your payment has been processed successfully!
                    </div>
                    <p>Thank you for your purchase. We've sent you a confirmation email with details.</p>
                    <a href="/" class="btn btn-primary">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

```

#### `resources/views/payment/cancel.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Payment Cancelled</div>

                <div class="card-body">
                    <div class="alert alert-warning">
                        You cancelled the payment process.
                    </div>
                    <p>No charges have been made to your account.</p>
                    <a href="{{ route('payment.form') }}" class="btn btn-primary">Try Again</a>
                    <a href="/" class="btn btn-secondary">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### `resources/views/payment/error.blade.php`

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Payment Error</div>

                <div class="card-body">
                    <div class="alert alert-danger">
                        An error occurred during the payment process.
                    </div>
                    <p>{{ session('error') ?? 'Something went wrong with your payment. Please try again or contact support if the problem persists.' }}</p>
                    <a href="{{ route('payment.form') }}" class="btn btn-primary">Try Again</a>
                    <a href="/" class="btn btn-secondary">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## 🛣️ Step 6: Define Routes

Edit `routes/web.php`:

```php
use App\Http\Controllers\PaypalController;

Route::get('/payment/success-page', function () {
    return view('payment.success');
})->name('payment.success');

Route::get('/payment/cancel-page', function () {
    return view('payment.cancel');
})->name('payment.cancel');

Route::get('/payment/error-page', function () {
    return view('payment.error');
})->name('payment.error');

Route::get('/payment/form', function () {
    return view('payment.form');
})->name('payment.form');

Route::post('/paypal/create', [PaypalController::class, 'createPayment'])->name('paypal.create');
```

---

## 🧪 Step 7: Test the Payment Using a Personal Sandbox Account

To test payments:

1. Go to **[PayPal Sandbox Accounts](https://developer.paypal.com/dashboard/accounts)**.
2. Use the credentials (username & password) for the **Personal** sandbox account to simulate a buyer.
3. After making a payment, **check the Business account** in the same dashboard to confirm the payment was received.

This allows you to fully test the flow from payment creation to PayPal redirection and success confirmation before going live.

---

✅ You’re all set! You now have a complete PayPal integration in Laravel ready to test and deploy.