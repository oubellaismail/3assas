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
