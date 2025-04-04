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
                    <a href="{{ route('stripe.form') }}" class="btn btn-primary">Try Again</a>
                    <a href="/" class="btn btn-secondary">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection