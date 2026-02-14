@extends('emails.layout')

@section('title', 'New Discount Request')

@section('content')
    <h1>New Discount Request</h1>

    <p>Hello Admin,</p>

    <p>A new discount request has been submitted and requires your review.</p>

    <div class="info-box">
        <p><strong>Requested By:</strong> {{ $discountRequest->user->name }}</p>
        <p><strong>Email:</strong> {{ $discountRequest->user->email }}</p>
        <p><strong>Reason:</strong> {{ $discountRequest->reason }}</p>
        <p><strong>Submitted:</strong> {{ $discountRequest->created_at->format('F j, Y \a\t g:i A') }}</p>
    </div>

    <p>
        <a href="{{ url("/admin/discount-requests/{$discountRequest->id}") }}" class="btn">Review Request</a>
    </p>

    <p>Please review and approve or deny this request.</p>
@endsection
