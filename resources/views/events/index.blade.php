<x-public-layout title="Events - NADA">

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-900">NADA Events</h1>
            <p class="mt-2 text-lg text-gray-600">Browse upcoming events and register today.</p>
        </div>

        {{-- Featured Events --}}
        @if ($featuredEvents->count())
            <div class="mb-12">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Featured Events</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($featuredEvents as $event)
                        <a href="{{ route('public.events.show', $event->slug) }}" class="block group">
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                @if ($event->featured_image_path)
                                    <img src="{{ asset('storage/' . $event->featured_image_path) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-brand-primary flex items-center justify-center">
                                        <svg class="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 group-hover:text-brand-primary transition-colors">{{ $event->title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ $event->start_date->format('M j, Y') }}</p>
                                    <p class="text-sm text-gray-500">{{ $event->location_display }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Search & Filters --}}
        <div class="bg-white rounded-lg shadow p-4 mb-8">
            <form method="GET" action="{{ route('public.events.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Search events..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                </div>
                <div class="w-full sm:w-auto">
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                </div>
                <div class="w-full sm:w-auto">
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-brand-primary text-white text-sm font-medium rounded-md hover:bg-brand-primary-hover transition-colors">
                        Search
                    </button>
                    @if (request('search') || request('date_from') || request('date_to'))
                        <a href="{{ route('public.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-200 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- All Events --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($events as $event)
                <a href="{{ route('public.events.show', $event->slug) }}" class="block group">
                    <div class="bg-white rounded-lg shadow hover:shadow-md transition-shadow p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="text-sm font-medium text-brand-secondary">
                                    {{ $event->start_date->format('M j, Y') }}
                                </div>
                                <h3 class="mt-1 text-lg font-semibold text-gray-900 group-hover:text-brand-primary transition-colors">
                                    {{ $event->title }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $event->location_display }}</p>
                                @if ($event->short_description)
                                    <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $event->short_description }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                {{ $event->date_display }}
                            </span>
                            @if ($event->isFull())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Full</span>
                            @elseif ($event->isRegistrationOpen())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Open</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12">
                    @if (request('search') || request('date_from') || request('date_to'))
                        <p class="text-gray-500">No events match your search. <a href="{{ route('public.events.index') }}" class="text-brand-primary hover:underline">Clear filters</a></p>
                    @else
                        <p class="text-gray-500">No upcoming events at this time. Check back soon!</p>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $events->links() }}
        </div>
    </div>
</x-public-layout>
