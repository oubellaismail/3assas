<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\StripeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'getLogin']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/register', [AuthController::class, 'showForm']);
Route::post('/register', [AuthController::class, 'register']);


//paypal
Route::get('/payment', function() {
    return view('payment.form');
})->name('payment.form');

Route::post('/payment/process', [PaypalController::class, 'createPayment'])->name('payment.process');
Route::get('/payment/success', [PayPalController::class, 'paymentSuccess'])->name('paypal.success');
Route::get('/payment/cancel', [PayPalController::class, 'paymentCancel'])->name('paypal.cancel');

Route::get('/payment/success-page', function() {
    return view('payment.success');
})->name('payment.success');

Route::get('/payment/cancel-page', function() {
    return view('payment.cancel');
})->name('payment.cancel');

Route::get('/payment/error-page', function() {
    return view('payment.error');
})->name('payment.error');


//stripe

Route::get('/stripe', [StripeController::class, 'index'])->name('stripe.form');
Route::post('/stripe/checkout', [StripeController::class, 'checkout'])->name('stripe.checkout');
Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
Route::get('/stripe/cancel', [StripeController::class, 'cancel'])->name('stripe.cancel');
Route::get('/payment/error', function() {
    return view('payment.error');
})->name('payment.error');


Route::get('/admin/dashboard', function () {
    return response()->json([
        "message" => "Yes"
    ]);
})->middleware('admin');