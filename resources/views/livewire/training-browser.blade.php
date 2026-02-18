<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-brand-primary">Browse Trainings</h2>
                <p class="mt-1 text-gray-600">Find and register for upcoming NADA trainings.</p>
            </div>

            {{-- Filters --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               placeholder="Search trainings by title, description, or location..."
                               class="w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 text-sm">
                    </div>
                    <div class="sm:w-48">
                        <select wire:model.live="type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 text-sm">
                            <option value="">All Types</option>
                            @foreach ($trainingTypes as $trainingType)
                                <option value="{{ $trainingType->value }}">{{ $trainingType->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Training Cards Grid --}}
            @if ($trainings->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No trainings found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($trainings as $training)
                        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden flex flex-col hover:shadow-md transition-shadow">
                            <div class="p-6 flex-1">
                                {{-- Type Badge --}}
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white bg-brand-primary">
                                        {{ $training->type->label() }}
                                    </span>
                                    <span class="text-sm font-semibold text-brand-secondary">
                                        {{ $training->price_formatted }}
                                    </span>
                                </div>

                                {{-- Title --}}
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $training->title }}</h3>

                                {{-- Description --}}
                                <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ $training->description }}</p>

                                {{-- Details --}}
                                <div class="space-y-2 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                        {{ $training->start_date->format('M d, Y \a\t g:i A') }}
                                    </div>
                                    @if ($training->location_name)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                            </svg>
                                            {{ $training->location_name }}
                                        </div>
                                    @endif
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                        </svg>
                                        Trainer: {{ $training->trainer->full_name }}
                                    </div>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    @php
                                        $spots = $training->spotsRemaining();
                                    @endphp
                                    @if ($spots !== null)
                                        <span class="text-xs text-gray-500">{{ $spots }} {{ Str::plural('spot', $spots) }} remaining</span>
                                    @else
                                        <span class="text-xs text-gray-500">Open enrollment</span>
                                    @endif
                                    <a href="{{ route('trainings.show', $training) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-md text-white text-xs font-medium hover:opacity-90 transition-opacity bg-brand-secondary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $trainings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
