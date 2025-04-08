# 💳 Laravel Stripe Integration Guide

This guide walks you through setting up Stripe payments in Laravel using the `stripe/stripe-php` SDK.

---

## 📦 Step 1: Install the Stripe PHP SDK

```bash
composer require stripe/stripe-php
```

---

## 🧪 Step 2: Set Up a Stripe Account

1. Go to [Stripe Dashboard](https://dashboard.stripe.com/register)
2. Create a test account.
3. Navigate to **Developers > API keys**.
4. Copy your **Publishable key** and **Secret key**.

---

Here's the corrected and polished version of that section for your Markdown documentation:

---

## 🔐 Configure Stripe in `.env`

Add the following keys to your `.env` file:

```env
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_CURRENCY=usd # Choose your preferred currency (e.g., usd, eur, gbp)
```

Create config/stripe.php file : 
```bash
touch config/stripe.php
```

Paste this : 

```php
<?php

return [
    'pk_key' => env('STRIPE_KEY', ''),
    'sk_key' => env('STRIPE_SECRET', ''),
    'currency' => env('STRIPE_CURRENCY', 'usd'),
];

```

> ⚠️ **Keep your secret key secure. Never expose it in your frontend or client-side code.**

--- 

## 🧾 Step 4: Create Stripe Controller

```bash
php artisan make:controller StripeController
```

Add the following logic to `app/Http/Controllers/StripeController.php`:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;

class StripeController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => $request->description,
                    ],
                    'unit_amount' => $request->amount * 100, // Stripe accepts cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('stripe.success'),
            'cancel_url' => route('stripe.cancel'),
        ]);

        return redirect($session->url);
    }

    public function success()
    {
        return view('payment.success');
    }

    public function cancel()
    {
        return view('payment.cancel');
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
    <title>{{ config('app.name', 'Laravel Stripe') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @yield('content')
    </div>
</body>
</html>
```

---

### 📑 Blade Files

#### `resources/views/payment/form.blade.php`

```blade
@extends('layout.app')

@section('content')
<div class="card">
    <div class="card-header">Stripe Payment</div>
    <div class="card-body">
        <form method="POST" action="{{ route('stripe.checkout') }}">
            @csrf

            <div class="form-group mb-3">
                <label>Amount ($)</label>
                <input type="number" name="amount" class="form-control" required min="1" step="0.01">
            </div>

            <div class="form-group mb-3">
                <label>Description</label>
                <input type="text" name="description" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Pay with Stripe</button>
        </form>
    </div>
</div>
@endsection
```

#### `resources/views/payment/success.blade.php`

```blade
@extends('layout.app')

@section('content')
<div class="alert alert-success">
    Payment Successful! Thank you for your purchase.
</div>
<a href="/" class="btn btn-primary">Return Home</a>
@endsection
```

#### `resources/views/payment/cancel.blade.php`

```blade
@extends('layout.app')

@section('content')
<div class="alert alert-warning">
    Payment Cancelled. You didn’t complete the process.
</div>
<a href="{{ route('payment.form') }}" class="btn btn-primary">Try Again</a>
@endsection
```

---

## 🛣️ Step 6: Define Routes

Edit your `routes/web.php`:

```php
use App\Http\Controllers\StripeController;

Route::get('/payment/form', function () {
    return view('payment.form');
})->name('payment.form');

Route::post('/stripe/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout');
Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
```

---

## 🧪 Step 7: Test Your Payment

1. Visit `http://your-app.test/payment/form`
2. Use one of Stripe's test cards:
   - Number: `4242 4242 4242 4242`
   - Exp: Any future date
   - CVC: Any 3 digits

---

✅ You're all set! You’ve now integrated Stripe payments into your Laravel app using the test environment. When going live, just switch the keys in `.env` to your live keys.