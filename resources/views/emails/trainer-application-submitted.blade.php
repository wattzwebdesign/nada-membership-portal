@extends('emails.layout')

@section('title', 'New Trainer Application')

@section('content')
    <h1>New Trainer Application</h1>

    <p>Hello Admin,</p>

    <p>A new trainer application has been submitted and requires your review.</p>

    <div class="info-box">
        <p><strong>Applicant:</strong> {{ $application->user->name }}</p>
        <p><strong>Email:</strong> {{ $application->user->email }}</p>
        <p><strong>Submitted:</strong> {{ $application->created_at->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <p>
        <a href="{{ url("/admin/trainer-applications/{$application->id}") }}" class="btn">Review Application</a>
    </p>

    <p>Please review and process this application at your earliest convenience.</p>
@endsection
