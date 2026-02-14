@extends('emails.layout')

@section('title', 'Payment Failed')

@section('content')
    <h1>Payment Failed - Action Required</h1>

    <p>Hello {{ $invoice->user->name }},</p>

    <p>We were unable to process your recent payment. Please update your payment method to avoid any interruption to your membership.</p>

    <div class="info-box">
        <p><strong>Amount Due:</strong> ${{ number_format($invoice->amount, 2) }}</p>
        <p><strong>Date:</strong> {{ $invoice->created_at->format('F j, Y') }}</p>
    </div>

    <p>
        <a href="{{ url('/membership/payment-method') }}" class="btn">Update Payment Method</a>
    </p>

    <p>If you believe this is an error, please contact our support team for assistance.</p>
@endsection
