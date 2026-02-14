@extends('emails.layout')

@section('title', 'Payout Received!')

@section('content')
    <h1>Payout Received!</h1>

    <p>Hello {{ $user->name }},</p>

    <p>A payout has been processed to your account.</p>

    <div class="info-box">
        <p><strong>Amount:</strong> ${{ number_format($amount, 2) }} {{ $currency }}</p>
    </div>

    <p>The funds should appear in your bank account within a few business days, depending on your financial institution.</p>

    <p>
        <a href="{{ url('/dashboard') }}" class="btn">View Payout History</a>
    </p>

    <p>Thank you for being a valued NADA trainer!</p>
@endsection
