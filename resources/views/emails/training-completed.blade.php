@extends('emails.layout')

@section('title', 'Training Completed!')

@section('content')
    <h1>Training Completed!</h1>

    <p>Congratulations {{ $registration->user->name }}!</p>

    <p>You have successfully completed your NADA training session.</p>

    <div class="info-box">
        <p><strong>Training:</strong> {{ $registration->training->title }}</p>
        <p><strong>Completed:</strong> {{ now()->format('F j, Y') }}</p>
    </div>

    <p>Your certificate will be available shortly. You will receive another notification once it is ready for download.</p>

    <p>
        <a href="{{ url('/dashboard') }}" class="btn">View Your Dashboard</a>
    </p>

    <p>Thank you for your commitment to the NADA protocol.</p>
@endsection
