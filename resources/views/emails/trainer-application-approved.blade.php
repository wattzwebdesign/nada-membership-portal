@extends('emails.layout')

@section('title', 'Trainer Application Approved!')

@section('content')
    <h1>Trainer Application Approved!</h1>

    <p>Congratulations {{ $application->user->name }}!</p>

    <p>Your application to become a NADA trainer has been approved!</p>

    <p>You now have access to trainer features, including:</p>

    <div class="info-box">
        <p><strong>Create Trainings</strong> - Set up and manage your own training sessions</p>
        <p><strong>Manage Registrations</strong> - View and manage attendee registrations</p>
        <p><strong>Issue Certificates</strong> - Issue completion certificates to attendees</p>
        <p><strong>Receive Payouts</strong> - Earn revenue from your training sessions</p>
    </div>

    <p>
        <a href="{{ url('/dashboard') }}" class="btn">Go to Trainer Dashboard</a>
    </p>

    <p>Welcome to the NADA trainer community!</p>
@endsection
