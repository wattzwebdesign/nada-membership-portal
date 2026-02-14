@extends('emails.layout')

@section('title', 'Training Registration Confirmed')

@section('content')
    <h1>Training Registration Confirmed</h1>

    <p>Hello {{ $registration->user->name }},</p>

    <p>Your training registration has been confirmed. Here are your training details:</p>

    <div class="info-box">
        <p><strong>Training:</strong> {{ $registration->training->title }}</p>
        <p><strong>Date:</strong> {{ $registration->training->start_date->format('F j, Y') }}</p>
        <p><strong>Location:</strong> {{ $registration->training->location }}</p>
        @if($registration->training->trainer)
            <p><strong>Trainer:</strong> {{ $registration->training->trainer->name }}</p>
        @endif
    </div>

    <p>
        <a href="{{ url("/trainings/{$registration->training->id}") }}" class="btn">View Training Details</a>
    </p>

    <p>We look forward to seeing you there!</p>
@endsection
