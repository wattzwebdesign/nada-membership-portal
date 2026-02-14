@extends('emails.layout')

@section('title', 'Payment Method Updated')

@section('content')
    <h1>Payment Method Updated</h1>

    <p>Hello {{ $user->name }},</p>

    <p>Your payment method has been successfully updated. All future charges will be applied to your new payment method.</p>

    <p>
        <a href="{{ url('/membership') }}" class="btn">View Account</a>
    </p>

    <p>If you did not make this change, please contact our support team immediately.</p>
@endsection
