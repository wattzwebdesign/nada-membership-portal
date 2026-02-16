<x-public-layout>
    <x-slot name="title">{{ $trainer->full_name }} - NADA Trainer</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Back Link --}}
        <a href="{{ route('public.trainers.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Trainer Directory
        </a>

        {{-- Profile Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-start gap-6">
                {{-- Photo / Initials --}}
                @if ($trainer->profile_photo_url)
                    <img src="{{ $trainer->profile_photo_url }}" alt="{{ $trainer->full_name }}" class="h-24 w-24 rounded-full object-cover flex-shrink-0">
                @else
                    <div class="h-24 w-24 rounded-full flex items-center justify-center text-white text-2xl font-bold flex-shrink-0" style="background-color: #374269;">
                        {{ $trainer->initials }}
                    </div>
                @endif

                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold text-gray-900">{{ $trainer->full_name }}</h1>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium mt-2" style="background-color: #d39c27; color: white;">
                        NADA Registered Trainer
                    </span>

                    <div class="mt-4 space-y-2">
                        @if ($trainer->location_display)
                            <p class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $trainer->location_display }}
                            </p>
                        @endif

                        @if ($trainer->email)
                            <p class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <a href="mailto:{{ $trainer->email }}" class="hover:underline" style="color: #374269;">{{ $trainer->email }}</a>
                            </p>
                        @endif

                        @if ($trainer->phone)
                            <p class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <a href="tel:{{ $trainer->phone }}" class="hover:underline" style="color: #374269;">{{ $trainer->phone }}</a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Bio Section --}}
        @if ($trainer->bio)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sm:p-8 mt-6">
                <h2 class="text-lg font-semibold mb-3" style="color: #374269;">About</h2>
                <div class="text-sm text-gray-700 leading-relaxed">
                    {!! nl2br(e($trainer->bio)) !!}
                </div>
            </div>
        @endif

        {{-- Upcoming Trainings --}}
        @if ($upcomingTrainings->isNotEmpty())
            <div class="mt-8">
                <h2 class="text-lg font-semibold mb-4" style="color: #374269;">Upcoming Trainings</h2>

                <div class="space-y-4">
                    @foreach ($upcomingTrainings as $training)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                            {{ $training->type === \App\Enums\TrainingType::Virtual ? 'bg-purple-100 text-purple-800' : ($training->type === \App\Enums\TrainingType::Hybrid ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800') }}">
                                            {{ $training->type->label() }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-900">{{ $training->price_formatted }}</span>
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900">{{ $training->title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $training->start_date->format('M j, Y') }}
                                        @if ($training->end_date && !$training->start_date->isSameDay($training->end_date))
                                            &ndash; {{ $training->end_date->format('M j, Y') }}
                                        @endif
                                    </p>
                                    @if ($training->location_name)
                                        <p class="text-sm text-gray-500 mt-0.5">{{ $training->location_name }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('trainings.show', $training) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white whitespace-nowrap" style="background-color: #374269;">
                                    View Training
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-public-layout>
