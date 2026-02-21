<x-layouts.public>
    <x-slot:title>Registration Confirmed - NADA</x-slot:title>

    <div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
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
                        <div class="flex justify-center gap-4">
                            <a href="{{ route('events.wallet.apple', $event) }}" class="inline-flex items-center px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 text-sm font-medium">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                                Apple Wallet
                            </a>
                            <a href="{{ route('events.wallet.google', $event) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                Google Wallet
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
</x-layouts.public>
