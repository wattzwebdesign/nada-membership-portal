@extends('emails.layout')

@section('title', 'Subscription Confirmed')

@section('content')
    <h1>Subscription Confirmed</h1>

    <p>Hello {{ $subscription->user->name }},</p>

    <p>Your NADA membership subscription has been confirmed. Here are your subscription details:</p>

    <div class="info-box">
        <p><strong>Plan:</strong> {{ $subscription->plan->name }}</p>
        <p><strong>Status:</strong> Active</p>
        <p><strong>Start Date:</strong> {{ $subscription->current_period_start->format('F j, Y') }}</p>
        <p><strong>Next Billing Date:</strong> {{ $subscription->current_period_end->format('F j, Y') }}</p>
    </div>

    <p>
        <a href="{{ url('/membership') }}" class="btn">View Membership</a>
    </p>

    <p>Thank you for becoming a NADA member!</p>
@endsection
