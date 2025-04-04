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