@extends('emails.layout')

@section('title', 'Event Registration Confirmed')

@section('content')
    <h1>Event Registration Confirmed</h1>

    <p>Hello {{ $registration->full_name }},</p>

    <p>Your event registration has been confirmed. Here are your details:</p>

    <div class="info-box">
        <p><strong>Event:</strong> {{ $event->title }}</p>
        <p><strong>Date:</strong> {{ $event->start_date->format('F j, Y') }}</p>
        <p><strong>Location:</strong> {{ $event->location_display }}</p>
        <p><strong>Registration #:</strong> {{ $registration->registration_number }}</p>
        @if ($registration->total_amount_cents > 0)
            <p><strong>Total Paid:</strong> ${{ number_format($registration->total_amount_cents / 100, 2) }}</p>
        @endif
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <p style="font-weight: 600; color: #1C3519; margin-bottom: 12px;">Your Check-In QR Code</p>
        <img src="{{ $qrCodeUrl }}" alt="Check-In QR Code" width="250" height="250" style="border: 1px solid #e8e8e8; border-radius: 8px;">
        <p style="font-size: 13px; color: #888888; margin-top: 8px;">Present this QR code at the event for quick check-in.</p>
    </div>

    <p>
        <a href="{{ $confirmationUrl }}" class="btn">View Registration</a>
    </p>

    <p>We look forward to seeing you there!</p>
@endsection
