<x-public-layout :title="$event->title . ' - NADA'">

    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Event Details --}}
            <div class="lg:col-span-2 space-y-6">
                @if ($event->featured_image_path)
                    <img src="{{ asset('storage/' . $event->featured_image_path) }}" alt="{{ $event->title }}" class="w-full h-64 object-cover rounded-lg shadow">
                @endif

                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $event->title }}</h1>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm text-gray-600">
                        <div class="flex items-center gap-1">
                            <svg class="w-5 h-5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            {{ $event->date_display }}
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-5 h-5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ $event->location_display }}
                        </div>
                        @if ($event->spotsRemaining() !== null)
                            <div class="flex items-center gap-1">
                                <svg class="w-5 h-5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ $event->spotsRemaining() }} spots remaining
                            </div>
                        @endif
                    </div>
                </div>

                @if ($event->description)
                    <div class="prose max-w-none">
                        {!! $event->description !!}
                    </div>
                @endif

                @if ($event->latitude && $event->longitude)
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div id="event-map" class="w-full h-64"></div>
                        <div class="p-4">
                            @if ($event->location_name || $event->location_address)
                                <p class="text-sm font-medium text-gray-900">{{ $event->location_name }}</p>
                                @if ($event->location_address)
                                    <p class="text-sm text-gray-500">
                                        {{ $event->location_address }}@if($event->city), {{ $event->city }}@endif @if($event->state){{ $event->state }}@endif {{ $event->zip }}
                                    </p>
                                @endif
                            @endif
                            <div class="mt-3 flex flex-wrap gap-2">
                                @php
                                    $addressQuery = urlencode(collect([$event->location_name, $event->location_address, $event->city, $event->state, $event->zip])->filter()->implode(', '));
                                    $hotelUrl = 'https://www.google.com/maps/search/Hotels/@' . $event->latitude . ',' . $event->longitude . ',6053m';
                                @endphp
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $addressQuery }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md bg-brand-primary text-white hover:bg-brand-accent transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Get Directions
                                </a>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $addressQuery }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                    View on Google Maps
                                </a>
                                <a href="{{ $hotelUrl }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    Nearby Hotels
                                </a>
                            </div>
                        </div>
                    </div>
                @elseif ($event->location_address || $event->location_name)
                    {{-- No coordinates but has address — show address card with link buttons --}}
                    <div class="bg-white rounded-lg shadow p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-6 h-6 text-brand-secondary flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $event->location_name }}</p>
                                @if ($event->location_address)
                                    <p class="text-sm text-gray-500">
                                        {{ $event->location_address }}@if($event->city), {{ $event->city }}@endif @if($event->state){{ $event->state }}@endif {{ $event->zip }}
                                    </p>
                                @endif
                                @php
                                    $addressQuery = urlencode(collect([$event->location_name, $event->location_address, $event->city, $event->state, $event->zip])->filter()->implode(', '));
                                @endphp
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $addressQuery }}" target="_blank" class="text-sm text-brand-primary hover:underline">Get Directions</a>
                                    <span class="text-gray-300">|</span>
                                    <a href="https://www.google.com/maps/search/Hotels+near+{{ $addressQuery }}" target="_blank" class="text-sm text-brand-primary hover:underline">Nearby Hotels</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($event->virtual_link)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-sm text-blue-800"><strong>Virtual Link:</strong> This event includes a virtual component. The link will be shared upon registration.</p>
                    </div>
                @endif

                @if ($event->contact_email || $event->contact_phone)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="font-medium text-gray-900 mb-2">Contact</h3>
                        @if ($event->organizer_name)
                            <p class="text-sm text-gray-600">{{ $event->organizer_name }}</p>
                        @endif
                        @if ($event->contact_email)
                            <p class="text-sm text-gray-600"><a href="mailto:{{ $event->contact_email }}" class="text-brand-primary hover:underline">{{ $event->contact_email }}</a></p>
                        @endif
                        @if ($event->contact_phone)
                            <p class="text-sm text-gray-600">{{ $event->contact_phone }}</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Registration Form --}}
            <div class="lg:col-span-1">
                <div class="sticky top-8">
                    @if ($event->isRegistrationOpen())
                        <livewire:event-registration-form :event="$event" />
                    @elseif ($event->isFull())
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                            <h3 class="text-lg font-semibold text-red-800">Event Full</h3>
                            <p class="text-sm text-red-600 mt-1">This event has reached maximum capacity.</p>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                            <h3 class="text-lg font-semibold text-gray-800">Registration Closed</h3>
                            <p class="text-sm text-gray-600 mt-1">Registration is not currently open for this event.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($event->latitude && $event->longitude && config('services.google.maps_api_key'))
        @push('scripts')
        <script>
            function initEventMap() {
                const location = { lat: {{ $event->latitude }}, lng: {{ $event->longitude }} };
                const map = new google.maps.Map(document.getElementById('event-map'), {
                    center: location,
                    zoom: 15,
                    disableDefaultUI: true,
                    zoomControl: true,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: true,
                    styles: [
                        { featureType: 'poi', stylers: [{ visibility: 'simplified' }] },
                    ],
                });
                new google.maps.Marker({
                    position: location,
                    map: map,
                    title: @json($event->location_name ?: $event->title),
                });
            }

            (function() {
                if (window.google && window.google.maps) {
                    initEventMap();
                    return;
                }
                const script = document.createElement('script');
                script.src = 'https://maps.googleapis.com/maps/api/js?key={{ config("services.google.maps_api_key") }}&callback=initEventMap';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            })();
        </script>
        @endpush
    @endif
</x-public-layout>
