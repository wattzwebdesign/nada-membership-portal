<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $training->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            {{-- Badges --}}
                            <div class="flex flex-wrap gap-2 mb-4">
                                @php
                                    $typeBadgeColors = [
                                        'in_person' => 'bg-blue-100 text-blue-800',
                                        'virtual' => 'bg-purple-100 text-purple-800',
                                        'hybrid' => 'bg-indigo-100 text-indigo-800',
                                    ];
                                    $typeBadgeColor = $typeBadgeColors[$training->type->value] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                    {{ $training->type->label() }}
                                </span>
                                @if ($training->is_group)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        Invite Only
                                    </span>
                                @endif
                                @if ($training->is_paid)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: #fef3c7; color: #92400e;">
                                        Paid Training
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Free / Sponsored
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $training->title }}</h3>

                            @if ($training->description)
                                <div x-data="{ expanded: false }" class="mb-6">
                                    <div class="prose max-w-none text-gray-600 relative" :class="{ 'max-h-[7.5rem] overflow-hidden': !expanded }" id="description-container">
                                        {!! nl2br(e($training->description)) !!}
                                        <div x-show="!expanded" class="absolute bottom-0 left-0 right-0 h-10 bg-gradient-to-t from-white to-transparent"></div>
                                    </div>
                                    <button
                                        x-show="$el.parentElement.querySelector('#description-container').scrollHeight > 120"
                                        x-cloak
                                        @click="expanded = !expanded"
                                        class="mt-2 text-sm font-medium hover:underline text-brand-primary"
                                        x-text="expanded ? 'Show less' : 'See more'"
                                    ></button>
                                </div>
                            @endif

                            {{-- Details Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-200 pt-6">
                                {{-- Date & Time --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Date & Time</p>
                                        <p class="text-sm text-gray-500">{{ $training->start_date->format('l, F j, Y') }}</p>
                                        <p class="text-sm text-gray-500">{{ $training->start_date->format('g:i A') }} - {{ $training->end_date->format('g:i A') }} {{ $training->timezone }}</p>
                                    </div>
                                </div>

                                {{-- Location --}}
                                @if ($training->type !== App\Enums\TrainingType::Virtual)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Location</p>
                                            @if ($training->location_name)
                                                <p class="text-sm text-gray-500">{{ $training->location_name }}</p>
                                            @endif
                                            @if ($training->location_address)
                                                <p class="text-sm text-gray-500">{{ $training->location_address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Virtual Link --}}
                                @if (in_array($training->type, [App\Enums\TrainingType::Virtual, App\Enums\TrainingType::Hybrid]) && $training->virtual_link)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Virtual Meeting</p>
                                            @if ($userRegistration)
                                                <a href="{{ $training->virtual_link }}" target="_blank" class="text-sm hover:underline text-brand-primary">Join Meeting</a>
                                            @else
                                                <p class="text-sm text-gray-500">Link available after registration</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Trainer --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Trainer</p>
                                        <p class="text-sm text-gray-500">{{ $training->trainer->full_name ?? 'N/A' }}</p>
                                    </div>
                                </div>

                                {{-- Capacity --}}
                                @if ($training->max_attendees)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Capacity</p>
                                            <p class="text-sm text-gray-500">{{ $training->registrations_count ?? 0 }} / {{ $training->max_attendees }} registered</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar: Registration --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold mb-4 text-brand-primary">Registration</h4>

                            @if ($training->is_paid)
                                <div class="text-center mb-4">
                                    <span class="text-3xl font-extrabold text-brand-primary">${{ number_format($training->price_cents / 100, 2) }}</span>
                                    <span class="text-sm text-gray-500">per person</span>
                                </div>
                            @else
                                <div class="text-center mb-4">
                                    <span class="text-3xl font-extrabold text-green-600">Free</span>
                                </div>
                            @endif

                            @if ($userRegistration)
                                <div class="bg-green-50 border border-green-200 rounded-md p-3 mb-4">
                                    <p class="text-sm text-green-700 font-medium text-center">You are registered for this training.</p>
                                </div>

                                @if ($userRegistration->status === App\Enums\RegistrationStatus::Registered && $training->start_date->isFuture())
                                    <div class="mb-4 border-t border-gray-200 pt-4">
                                        <p class="text-sm font-semibold mb-2">Add to Wallet</p>
                                        <p class="text-xs text-gray-500 mb-3">Get a reminder before your training.</p>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <a href="{{ route('trainings.wallet.apple', $training) }}">
                                                <img src="{{ asset('images/add-to-apple-wallet.svg') }}" alt="Add to Apple Wallet" class="h-11">
                                            </a>
                                            <a href="{{ route('trainings.wallet.google', $training) }}">
                                                <img src="{{ asset('images/add-to-google-wallet.svg') }}" alt="Add to Google Wallet" class="h-11">
                                            </a>
                                        </div>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('trainings.cancel-registration', $training) }}" onsubmit="return confirm('Are you sure you want to cancel your registration?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50 transition">
                                        Cancel Registration
                                    </button>
                                </form>
                            @elseif (auth()->check() && ! auth()->user()->hasActiveSubscription())
                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                                    <p class="text-sm text-yellow-700 font-medium text-center">An active membership plan is required to register for trainings.</p>
                                </div>
                                <a href="{{ route('membership.index') }}" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white transition bg-brand-primary">
                                    View Membership Plans
                                </a>
                            @elseif ($training->max_attendees && ($training->registrations_count ?? 0) >= $training->max_attendees)
                                <div class="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
                                    <p class="text-sm text-yellow-700 font-medium text-center">This training is full.</p>
                                </div>
                                <button disabled class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-50 cursor-not-allowed">
                                    Training Full
                                </button>
                            @else
                                <form method="POST" action="{{ route('trainings.register', $training) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white transition bg-brand-secondary hover:opacity-90">
                                        {{ $training->is_paid ? 'Register & Pay' : 'Register Now' }}
                                    </button>
                                </form>
                            @endif

                            @if ($training->max_attendees)
                                <p class="mt-3 text-xs text-center text-gray-500">
                                    {{ max(0, $training->max_attendees - ($training->registrations_count ?? 0)) }} spots remaining
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Back Link --}}
                    <div class="mt-4">
                        <a href="{{ route('trainings.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Back to Trainings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
