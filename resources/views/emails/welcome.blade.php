@extends('emails.layout')

@section('title', 'Welcome to NADA!')

@section('content')
    <h1>Welcome to NADA!</h1>

    <p>Hello {{ $user->name }},</p>

    <p>
        Welcome to the National Acupuncture Detoxification Association membership portal.
        We are thrilled to have you join our community of dedicated practitioners committed
        to the NADA protocol.
    </p>

    <p>As a member, you will have access to:</p>

    <div class="info-box">
        <p><strong>Training Sessions</strong> - Register for upcoming NADA training programs</p>
        <p><strong>Certificates</strong> - Access and download your NADA certificates</p>
        <p><strong>Community</strong> - Connect with fellow NADA practitioners</p>
        <p><strong>Resources</strong> - Access exclusive member resources and materials</p>
    </div>

    <p>
        <a href="{{ url('/dashboard') }}" class="btn">Visit Your Dashboard</a>
    </p>

    <p>If you have any questions, please do not hesitate to reach out to our support team.</p>
@endsection
