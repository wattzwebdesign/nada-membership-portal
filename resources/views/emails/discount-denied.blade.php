@extends('emails.layout')

@section('title', 'Discount Request Update')

@section('content')
    <h1>Discount Request Update</h1>

    <p>Hello {{ $discountRequest->user->name }},</p>

    <p>We have reviewed your discount request and unfortunately we are unable to approve it at this time.</p>

    <p>If you believe this decision was made in error or if your circumstances have changed, please feel free to submit a new request with additional supporting information.</p>

    <p>
        <a href="{{ url('/membership') }}" class="btn">View Membership Plans</a>
    </p>

    <p>Thank you for your understanding.</p>
@endsection
