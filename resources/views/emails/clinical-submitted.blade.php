@extends('emails.layout')

@section('title', 'New Clinical Submission')

@section('content')
    <h1>New Clinical Submission</h1>

    <p>Hello Admin,</p>

    <p>A new clinical submission has been received and requires your review.</p>

    <div class="info-box">
        <p><strong>Submitted By:</strong> {{ $clinical->user->name }}</p>
        <p><strong>Email:</strong> {{ $clinical->user->email }}</p>
        <p><strong>Submitted:</strong> {{ $clinical->created_at->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <p>
        <a href="{{ url("/admin/clinicals/{$clinical->id}") }}" class="btn">Review Submission</a>
    </p>

    <p>Please review this clinical submission at your earliest convenience.</p>
@endsection
