<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaypalController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'getLogin']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);

Route::get('/register', [AuthController::class, 'showForm']);
Route::post('/register', [AuthController::class, 'register']);

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