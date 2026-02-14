@extends('emails.layout')

@section('title', 'Training Canceled')

@section('content')
    <h1>Training Canceled</h1>

    <p>Hello {{ $user->name }},</p>

    <p>We regret to inform you that the following training session has been canceled.</p>

    <div class="info-box">
        <p><strong>Training:</strong> {{ $training->title }}</p>
        <p><strong>Originally Scheduled:</strong> {{ $training->start_date->format('F j, Y') }}</p>
        <p><strong>Location:</strong> {{ $training->location }}</p>
    </div>

    <p>If you have already paid for this training, a refund will be processed automatically to your original payment method.</p>

    <p>
        <a href="{{ url('/trainings') }}" class="btn">Browse Other Trainings</a>
    </p>

    <p>We apologize for any inconvenience and hope to see you at a future training session.</p>
@endsection
