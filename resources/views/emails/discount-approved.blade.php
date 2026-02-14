@extends('emails.layout')

@section('title', 'Discount Request Approved!')

@section('content')
    <h1>Discount Request Approved!</h1>

    <p>Hello {{ $discountRequest->user->name }},</p>

    <p>Great news! Your discount request has been approved.</p>

    <div class="info-box">
        <p><strong>Discount Code:</strong> {{ $discountRequest->discount_code }}</p>
    </div>

    <p>You can apply this code during checkout to receive your discount on membership plans.</p>

    <p>
        <a href="{{ url('/membership') }}" class="btn">View Membership Plans</a>
    </p>

    <p>Thank you for being part of the NADA community!</p>
@endsection
