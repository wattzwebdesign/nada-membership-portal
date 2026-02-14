@extends('emails.layout')

@section('title', 'Trainer Application Update')

@section('content')
    <h1>Trainer Application Update</h1>

    <p>Hello {{ $application->user->name }},</p>

    <p>We have reviewed your trainer application and unfortunately we are unable to approve it at this time.</p>

    <p>This may be due to insufficient training hours or other requirements that have not yet been met.</p>

    <p>You are welcome to reapply once you have completed the necessary requirements.</p>

    <p>
        <a href="{{ url('/trainers/apply') }}" class="btn">View Requirements</a>
    </p>

    <p>Thank you for your interest in becoming a NADA trainer.</p>
@endsection
