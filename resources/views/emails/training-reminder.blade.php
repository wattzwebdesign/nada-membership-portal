@extends('emails.layout')

@section('title', 'Training Reminder')

@section('content')
    <h1>Training Reminder - Tomorrow!</h1>

    <p>Hello {{ $registration->user->name }},</p>

    <p>This is a friendly reminder that your NADA training session is tomorrow.</p>

    <div class="info-box">
        <p><strong>Training:</strong> {{ $training->title }}</p>
        <p><strong>Date:</strong> {{ $training->start_date->format('F j, Y') }}</p>
        <p><strong>Location:</strong> {{ $training->location }}</p>
        @if($training->trainer)
            <p><strong>Trainer:</strong> {{ $training->trainer->name }}</p>
        @endif
    </div>

    <p>Please arrive on time and bring any required materials.</p>

    <p>
        <a href="{{ url("/trainings/{$training->id}") }}" class="btn">View Training Details</a>
    </p>
@endsection
