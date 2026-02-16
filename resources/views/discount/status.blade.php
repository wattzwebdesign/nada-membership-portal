<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Discount Request Status') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            @if ($discountRequests->count() > 0)
                <div class="space-y-6">
                    @foreach ($discountRequests as $discountRequest)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-lg font-semibold" style="color: #374269;">
                                        {{ $discountRequest->discount_type->label() }} Discount Request
                                    </h3>
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'denied' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusColor = $statusColors[$discountRequest->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ ucfirst($discountRequest->status) }}
                                    </span>
                                </div>

                                {{-- Status Banner --}}
                                @if ($discountRequest->status === 'pending')
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-yellow-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-sm text-yellow-600">Your request is being reviewed. You will be notified by email once a decision is made.</p>
                                        </div>
                                    </div>
                                @elseif ($discountRequest->status === 'approved')
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-sm text-green-600">Approved! You can now access discounted membership plans.</p>
                                        </div>
                                    </div>
                                @elseif ($discountRequest->status === 'denied')
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-center">
                                            <svg class="h-5 w-5 text-red-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <p class="text-sm text-red-600">Your request was not approved. You may submit a new request with additional documentation.</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Request Details --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="text-gray-500">Discount Type</p>
                                        <p class="font-medium text-gray-900">{{ $discountRequest->discount_type->label() }}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Submitted</p>
                                        <p class="font-medium text-gray-900">{{ $discountRequest->created_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>

                                    @if ($discountRequest->school_name)
                                        <div>
                                            <p class="text-gray-500">School</p>
                                            <p class="font-medium text-gray-900">{{ $discountRequest->school_name }}</p>
                                        </div>
                                    @endif

                                    @if ($discountRequest->years_remaining)
                                        <div>
                                            <p class="text-gray-500">Years Remaining as Student</p>
                                            <p class="font-medium text-gray-900">{{ $discountRequest->years_remaining }}</p>
                                        </div>
                                    @endif

                                    @if ($discountRequest->date_of_birth)
                                        <div>
                                            <p class="text-gray-500">Date of Birth</p>
                                            <p class="font-medium text-gray-900">{{ $discountRequest->date_of_birth->format('m/d/Y') }}</p>
                                        </div>
                                    @endif

                                    @if ($discountRequest->reviewed_at)
                                        <div>
                                            <p class="text-gray-500">Reviewed</p>
                                            <p class="font-medium text-gray-900">{{ $discountRequest->reviewed_at->format('M j, Y \a\t g:i A') }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if ($discountRequest->proof_description)
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-sm text-gray-500">Your Notes</p>
                                        <p class="text-sm text-gray-900 mt-1">{{ $discountRequest->proof_description }}</p>
                                    </div>
                                @endif

                                @if ($discountRequest->admin_notes)
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-sm text-gray-500">Admin Notes</p>
                                        <p class="text-sm text-gray-900 mt-1">{{ $discountRequest->admin_notes }}</p>
                                    </div>
                                @endif

                                {{-- Uploaded Documents --}}
                                @php $media = $discountRequest->getMedia('proof_documents'); @endphp
                                @if ($media->count() > 0)
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <p class="text-sm text-gray-500 mb-2">Uploaded Documents</p>
                                        <ul class="space-y-1">
                                            @foreach ($media as $file)
                                                <li class="flex items-center text-sm">
                                                    <svg class="w-4 h-4 mr-2 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                                                    <span class="text-gray-700">{{ $file->file_name }}</span>
                                                    <span class="ml-2 text-gray-400 text-xs">({{ number_format($file->size / 1024, 1) }} KB)</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Actions --}}
                                @if ($discountRequest->status === 'approved')
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <a href="{{ route('membership.plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #d39c27;">
                                            View Discounted Plans
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Submit another if latest is denied --}}
                @if ($discountRequests->first()->status === 'denied')
                    <div class="mt-6 text-center">
                        <a href="{{ route('discount.request.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                            Submit New Request
                        </a>
                    </div>
                @endif
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Discount Requests</h3>
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
