@extends('emails.layout')

@section('title', 'Membership Renewed')

@section('content')
    <h1>Membership Renewed</h1>

    <p>Hello {{ $subscription->user->name }},</p>

    <p>Your NADA membership has been successfully renewed. Here are your updated subscription details:</p>

    <div class="info-box">
        <p><strong>Plan:</strong> {{ $subscription->plan->name }}</p>
        <p><strong>Status:</strong> Active</p>
        <p><strong>Next Renewal Date:</strong> {{ $subscription->current_period_end->format('F j, Y') }}</p>
    </div>

    <p>
        <a href="{{ url('/membership') }}" class="btn">View Membership</a>
    </p>

    <p>Thank you for continuing your membership with NADA!</p>
@endsection
