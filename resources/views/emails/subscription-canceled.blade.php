@extends('emails.layout')

@section('title', 'Subscription Canceled')

@section('content')
    <h1>Subscription Canceled</h1>

    <p>Hello {{ $subscription->user->name }},</p>

    <p>Your NADA membership subscription has been canceled.</p>

    <div class="info-box">
        <p><strong>Plan:</strong> {{ $subscription->plan->name }}</p>
        <p><strong>Access Until:</strong> {{ $subscription->current_period_end->format('F j, Y') }}</p>
    </div>

    <p>Your membership benefits will remain active until the date shown above. After that, you will lose access to member-only features.</p>

    <p>You can resubscribe at any time to regain full membership benefits.</p>

    <p>
        <a href="{{ url('/membership') }}" class="btn">Resubscribe</a>
    </p>

    <p>We hope to see you back soon.</p>
@endsection
