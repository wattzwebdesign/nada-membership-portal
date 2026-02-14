<x-public-layout>
    <x-slot name="title">Certificate Verification - NADA</x-slot>

    <div class="py-16">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold" style="color: #374269;">Certificate Verification</h1>
                <p class="mt-2 text-gray-600">Verify NADA Acupuncture Detox Specialist certifications.</p>
            </div>

            @if (isset($certificate) && $certificate)
                {{-- Certificate Found --}}
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    {{-- Status Header --}}
                    @if ($certificate->status === 'active')
                        <div class="px-6 py-4" style="background-color: #374269;">
                            <div class="flex items-center justify-center text-white">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-lg font-semibold">Verified - Active Certificate</span>
                            </div>
                        </div>
                    @elseif ($certificate->status === 'expired')
                        <div class="px-6 py-4 bg-red-600">
                            <div class="flex items-center justify-center text-white">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-lg font-semibold">Expired Certificate</span>
                            </div>
                        </div>
                    @elseif ($certificate->status === 'revoked')
                        <div class="px-6 py-4 bg-gray-600">
                            <div class="flex items-center justify-center text-white">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                <span class="text-lg font-semibold">Revoked Certificate</span>
                            </div>
                        </div>
                    @endif

                    {{-- Certificate Details --}}
                    <div class="p-8">
                        <div class="text-center mb-6">
                            <p class="text-sm text-gray-500 uppercase tracking-wide">National Acupuncture Detoxification Association</p>
                            <h2 class="mt-2 text-2xl font-bold text-gray-900">{{ $certificate->user->full_name }}</h2>
                            <p class="mt-1 text-gray-600">Acupuncture Detox Specialist</p>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">NADA ID #</dt>
                                    <dd class="mt-1 text-lg font-mono font-semibold" style="color: #374269;">{{ $certificate->certificate_code }}</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Status</dt>
                                    <dd class="mt-1">
                                        @if ($certificate->status === 'active')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">Active</span>
                                        @elseif ($certificate->status === 'expired')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Expired</span>
                                        @elseif ($certificate->status === 'revoked')
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">Revoked</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Date Issued</dt>
                                    <dd class="mt-1 text-base font-medium text-gray-900">{{ $certificate->date_issued->format('F j, Y') }}</dd>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Expiration Date</dt>
                                    <dd class="mt-1 text-base font-medium text-gray-900">
                                        {{ $certificate->expiration_date ? $certificate->expiration_date->format('F j, Y') : 'N/A' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-6 text-center">
                            <p class="text-xs text-gray-400">This verification was performed on {{ now()->format('F j, Y \a\t g:i A T') }}</p>
                        </div>
                    </div>
                </div>
            @elseif (isset($notFound) && $notFound)
                {{-- Certificate Not Found --}}
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    <div class="px-6 py-4 bg-yellow-500">
                        <div class="flex items-center justify-center text-white">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <span class="text-lg font-semibold">Certificate Not Found</span>
                        </div>
                    </div>
                    <div class="p-8 text-center">
                        <p class="text-gray-600">No certificate was found matching the code <span class="font-mono font-semibold">{{ $code ?? '' }}</span>.</p>
                        <p class="mt-2 text-sm text-gray-500">Please double-check the certificate code and try again. If you believe this is an error, contact NADA at <a href="mailto:{{ config('mail.from.address') }}" class="underline" style="color: #374269;">{{ config('mail.from.address') }}</a>.</p>
                    </div>
                </div>
            @endif

            {{-- Search Form --}}
            <div class="mt-8 bg-white shadow-sm rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4" style="color: #374269;">Verify a Certificate</h3>
                <form method="GET" action="{{ url('/verify') }}" class="flex flex-col sm:flex-row gap-3">
                    <input type="text" name="code" value="{{ $code ?? '' }}" placeholder="Enter NADA certificate code..." required class="flex-1 rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                    <button type="submit" class="inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                        Verify
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-public-layout>
