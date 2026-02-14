<x-public-layout title="Verify Certificate - NADA">
    <div class="py-12">
        <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Verify a Certificate</h1>
                <p class="mt-2 text-gray-600">Enter a certificate number to verify its authenticity.</p>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="GET" action="{{ route('public.verify') }}">
                    <div class="flex gap-3">
                        <input
                            type="text"
                            name="code"
                            value="{{ $code ?? '' }}"
                            placeholder="Enter certificate number"
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            autofocus
                        />
                        <button type="submit" class="inline-flex items-center px-5 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                            Verify
                        </button>
                    </div>
                </form>
            </div>

            @if($searched)
                <div class="mt-6">
                    @if($certificate)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h2 class="text-lg font-semibold text-green-800">Valid Certificate</h2>
                            </div>

                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-600">Certificate Number</dt>
                                    <dd class="text-gray-900 font-mono">{{ $certificate->certificate_code }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-600">Holder</dt>
                                    <dd class="text-gray-900">{{ $certificate->user->full_name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-600">Type</dt>
                                    <dd class="text-gray-900">{{ $certificate->type ?? 'NADA Certificate' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-600">Issued</dt>
                                    <dd class="text-gray-900">{{ $certificate->issued_at?->format('F j, Y') ?? 'N/A' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-600">Expires</dt>
                                    <dd class="text-gray-900">
                                        @if($certificate->expires_at)
                                            {{ $certificate->expires_at->format('F j, Y') }}
                                            @if($certificate->expires_at->isPast())
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Expired</span>
                                            @endif
                                        @else
                                            No expiration
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-medium text-gray-600">Status</dt>
                                    <dd>
                                        @if($certificate->revoked_at)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Revoked</span>
                                        @elseif($certificate->expires_at?->isPast())
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">Expired</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                            <div class="flex items-center gap-3">
                                <svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <h2 class="text-lg font-semibold text-red-800">Certificate Not Found</h2>
                                    <p class="text-sm text-red-600 mt-1">No certificate was found matching "<strong>{{ $code }}</strong>". Please check the number and try again.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-public-layout>
