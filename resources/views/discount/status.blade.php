<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Discount Request Status') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (isset($discountRequest) && $discountRequest)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-6" style="color: #374269;">Current Discount Request</h3>

                        {{-- Status Banner --}}
                        @if ($discountRequest->status === 'pending')
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-yellow-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">Pending Review</p>
                                        <p class="text-sm text-yellow-600">Your request is being reviewed by the NADA team. You will be notified by email once a decision is made.</p>
                                    </div>
                                </div>
                            </div>
                        @elseif ($discountRequest->status === 'approved')
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-green-800">Approved</p>
                                        <p class="text-sm text-green-600">Your discount request has been approved. You can now access discounted membership plans.</p>
                                    </div>
                                </div>
                            </div>
                        @elseif ($discountRequest->status === 'denied')
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                <div class="flex items-center">
                                    <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-red-800">Denied</p>
                                        <p class="text-sm text-red-600">Your discount request was not approved. If you believe this is an error, you may submit a new request with additional documentation.</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Request Details --}}
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Discount Type</p>
                                    <p class="text-base font-medium text-gray-900">{{ ucfirst($discountRequest->discount_type) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Status</p>
                                    @php
                                        $discountStatusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'denied' => 'bg-red-100 text-red-800',
                                        ];
                                        $discountStatusColor = $discountStatusColors[$discountRequest->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $discountStatusColor }}">
                                        {{ ucfirst($discountRequest->status) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Submitted</p>
                                    <p class="text-base text-gray-900">{{ $discountRequest->created_at->format('F j, Y \a\t g:i A') }}</p>
                                </div>
                                @if ($discountRequest->reviewed_at)
                                    <div>
                                        <p class="text-sm text-gray-500">Reviewed</p>
                                        <p class="text-base text-gray-900">{{ $discountRequest->reviewed_at->format('F j, Y \a\t g:i A') }}</p>
                                    </div>
                                @endif
                            </div>

                            @if ($discountRequest->proof_description)
                                <div class="border-t border-gray-200 pt-4">
                                    <p class="text-sm text-gray-500">Your Description</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $discountRequest->proof_description }}</p>
                                </div>
                            @endif

                            @if ($discountRequest->admin_notes)
                                <div class="border-t border-gray-200 pt-4">
                                    <p class="text-sm text-gray-500">Admin Notes</p>
                                    <p class="text-sm text-gray-900 mt-1">{{ $discountRequest->admin_notes }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="mt-6 border-t border-gray-200 pt-6 flex flex-wrap gap-3">
                            @if ($discountRequest->status === 'approved')
                                <a href="{{ route('membership.plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #d39c27;">
                                    View Discounted Plans
                                </a>
                            @elseif ($discountRequest->status === 'denied')
                                <a href="{{ route('discount.request.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                    Submit New Request
                                </a>
                            @endif
                            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                                Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Discount Request</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't submitted a discount request yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('discount.request.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                Request a Discount
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
