<div>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Training Header --}}
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white bg-brand-primary">
                                    {{ $training->type->label() }}
                                </span>
                                @php
                                    $statusColors = [
                                        'published' => 'bg-green-100 text-green-800',
                                        'canceled' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-blue-100 text-blue-800',
                                    ];
                                    $colorClass = $statusColors[$training->status->value] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colorClass }}">
                                    {{ $training->status->label() }}
                                </span>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $training->title }}</h1>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-bold text-brand-secondary">{{ $training->price_formatted }}</span>
                        </div>
                    </div>

                    {{-- Training Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="space-y-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                </svg>
                                <div>
                                    <p class="font-medium">{{ $training->start_date->format('l, F j, Y') }}</p>
                                    <p>{{ $training->start_date->format('g:i A') }} - {{ $training->end_date?->format('g:i A') ?? 'TBD' }}
                                        @if ($training->timezone)
                                            ({{ $training->timezone }})
                                        @endif
                                    </p>
                                </div>
                            </div>

                            @if ($training->location_name)
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-5 h-5 mr-3 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <div>
                                        <p class="font-medium">{{ $training->location_name }}</p>
                                        @if ($training->location_address)
                                            <p>{{ $training->location_address }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-5 h-5 mr-3 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <p>Trainer: <span class="font-medium">{{ $training->trainer->full_name }}</span></p>
                            </div>
                        </div>

                        <div>
                            @if ($training->description)
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Description</h3>
                                <p class="text-sm text-gray-600">{{ $training->description }}</p>
                            @endif

                            @php
                                $spots = $training->spotsRemaining();
                            @endphp
                            @if ($spots !== null)
                                <div class="mt-4 p-3 rounded-md {{ $spots === 0 ? 'bg-red-50' : 'bg-gray-50' }}">
                                    <p class="text-sm {{ $spots === 0 ? 'text-red-700 font-medium' : 'text-gray-600' }}">
                                        {{ $spots === 0 ? 'This training is full' : $spots . ' ' . Str::plural('spot', $spots) . ' remaining' }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Registration Action --}}
                    <div class="border-t border-gray-200 pt-6">
                        @if ($isRegistered)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-green-800">You are registered for this training</p>
                                        <p class="text-xs text-gray-500">Status: {{ $registration->status->label() }}</p>
                                    </div>
                                </div>
                                @if ($registration->status === \App\Enums\RegistrationStatus::Registered)
                                    <button wire:click="cancelRegistration"
                                            wire:confirm="Cancel your registration for this training?"
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium border border-red-300 text-red-700 bg-white hover:bg-red-50 transition-colors">
                                        <span wire:loading.remove wire:target="cancelRegistration">Cancel Registration</span>
                                        <span wire:loading wire:target="cancelRegistration">Processing...</span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <button wire:click="register"
                                    wire:confirm="Register for {{ $training->title }}?{{ $training->is_paid ? ' You will be charged ' . $training->price_formatted . '.' : '' }}"
                                    wire:loading.attr="disabled"
                                    @if ($training->isFull()) disabled @endif
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed bg-brand-primary">
                                <span wire:loading.remove wire:target="register">
                                    @if ($training->isFull())
                                        Training Full
                                    @elseif ($training->is_paid)
                                        Register &mdash; {{ $training->price_formatted }}
                                    @else
                                        Register for Free
                                    @endif
                                </span>
                                <span wire:loading wire:target="register">Processing...</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
