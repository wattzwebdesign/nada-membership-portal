<x-public-layout title="Registration Confirmed - NADA">

    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            {{-- Success Header --}}
            <div class="bg-brand-primary p-6 text-center">
                <svg class="w-16 h-16 text-white mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h1 class="mt-4 text-2xl font-bold text-white">Registration Confirmed!</h1>
                <p class="mt-1 text-green-100">{{ $registration->registration_number }}</p>
            </div>

            <div class="p-6 space-y-6">
                {{-- Event Details --}}
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $event->title }}</h2>
                    <div class="mt-2 space-y-1 text-sm text-gray-600">
                        <p>{{ $event->date_display }}</p>
                        <p>{{ $event->location_display }}</p>
                    </div>
                </div>

                {{-- Registrant Info --}}
                <div class="border-t pt-4">
                    <h3 class="font-medium text-gray-900 mb-2">Registration Details</h3>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="text-gray-500">Name</div>
                        <div class="text-gray-900">{{ $registration->full_name }}</div>
                        <div class="text-gray-500">Email</div>
                        <div class="text-gray-900">{{ $registration->email }}</div>
                        <div class="text-gray-500">Total</div>
                        <div class="text-gray-900 font-semibold">{{ $registration->total_formatted }}</div>
                        <div class="text-gray-500">Status</div>
                        <div class="text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $registration->status->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- QR Code --}}
                <div class="border-t pt-4 text-center">
                    <h3 class="font-medium text-gray-900 mb-3">Your Check-In QR Code</h3>
                    <div class="inline-block p-4 bg-white border-2 border-gray-200 rounded-lg">
                        <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="Check-in QR Code" class="w-64 h-64">
                    </div>
                    <p class="text-sm text-gray-500 mt-2">Present this QR code at the event for check-in</p>
                </div>

                {{-- Wallet Pass Buttons --}}
                @auth
                    <div class="border-t pt-4">
                        <h3 class="font-medium text-gray-900 mb-3 text-center">Add to Wallet</h3>
                        <div class="flex flex-wrap justify-center items-center gap-3">
                            <a href="{{ route('events.wallet.apple', $event) }}" data-umami-event="Apple Wallet - Event">
                                <img src="{{ asset('images/add-to-apple-wallet.svg') }}" alt="Add to Apple Wallet" class="h-11">
                            </a>
                            <a href="{{ route('events.wallet.google', $event) }}" data-umami-event="Google Wallet - Event">
                                <img src="{{ asset('images/add-to-google-wallet.svg') }}" alt="Add to Google Wallet" class="h-11">
                            </a>
                        </div>
                    </div>
                @endauth

                {{-- Custom Confirmation Message --}}
                @if ($event->confirmation_message)
                    <div class="border-t pt-4">
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! $event->confirmation_message !!}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('public.events.index') }}" class="text-brand-primary hover:underline text-sm">
                &larr; Back to Events
            </a>
        </div>
    </div>
</x-public-layout>
