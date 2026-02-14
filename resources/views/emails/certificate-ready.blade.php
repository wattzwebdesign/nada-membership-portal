@extends('emails.layout')

@section('title', 'Your NADA Certificate is Ready!')

@section('content')
    <h1>Your Certificate is Ready!</h1>

    <p>Congratulations {{ $certificate->user->name }}!</p>

    <p>Your NADA certificate has been issued and is ready for download.</p>

    <div class="info-box">
        <p><strong>Certificate Number:</strong> {{ $certificate->certificate_number }}</p>
        <p><strong>Issued Date:</strong> {{ $certificate->created_at->format('F j, Y') }}</p>
    </div>

    <p>
        <a href="{{ url("/certificates/{$certificate->id}") }}" class="btn">Download Certificate</a>
    </p>

    <p>Thank you for your dedication to the NADA protocol.</p>
@endsection
