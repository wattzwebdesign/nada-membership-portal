<x-public-layout>
    <x-slot name="title">Find a NADA Trainer</x-slot>

    {{-- Hero Banner --}}
    <div class="py-10 text-center text-white" style="background-color: #374269;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold">Find a NADA Trainer</h1>
            <p class="mt-2 text-blue-100 text-lg">Locate a NADA Registered Trainer near you</p>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
            <form method="GET" action="{{ route('public.trainers.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, city, state, or keyword..." class="w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                </div>
                <div class="flex gap-2">
                    <select name="sort" class="rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="city" {{ $sort === 'city' ? 'selected' : '' }}>City</option>
                        <option value="state" {{ $sort === 'state' ? 'selected' : '' }}>State</option>
                    </select>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                        Search
                    </button>
                    <a href="{{ route('public.trainers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="trainerDirectory()">
        <div class="flex flex-col lg:flex-row gap-6">
            {{-- Left Panel: Trainer Cards --}}
            <div class="w-full lg:w-1/4">
                <p class="text-sm text-gray-500 mb-3">{{ $trainers->total() }} trainer{{ $trainers->total() !== 1 ? 's' : '' }} found</p>

                <div class="space-y-3 lg:max-h-[calc(100vh-220px)] lg:overflow-y-auto lg:pr-2">
                    @forelse ($trainers as $trainer)
                        <a href="{{ route('public.trainers.show', $trainer) }}"
                           class="block bg-white border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition-all duration-150"
                           :class="{ 'ring-2 ring-blue-400': highlightedTrainerId === {{ $trainer->id }} }"
                           @mouseenter="bounceMarker({{ $trainer->id }})"
                           @mouseleave="highlightedTrainerId = null">
                            <div class="flex items-center space-x-3">
                                @if ($trainer->profile_photo_url)
                                    <img src="{{ $trainer->profile_photo_url }}" alt="{{ $trainer->full_name }}" class="h-10 w-10 rounded-full object-cover flex-shrink-0">
                                @else
                                    <div class="h-10 w-10 rounded-full flex items-center justify-center text-white text-sm font-semibold flex-shrink-0" style="background-color: #374269;">
                                        {{ $trainer->initials }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $trainer->full_name }}</p>
                                    @if ($trainer->location_display)
                                        <p class="text-xs text-gray-500 flex items-center mt-0.5">
                                            <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span class="truncate">{{ $trainer->location_display }}</span>
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <p class="text-sm">No trainers found matching your search.</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $trainers->links() }}
                </div>
            </div>

            {{-- Right Panel: Google Map --}}
            <div class="w-full lg:w-3/4">
                <div id="trainer-map" class="w-full h-[400px] lg:h-[calc(100vh-220px)] rounded-lg border border-gray-200 bg-gray-100"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    @if ($googleMapsApiKey)
    <script>
        function trainerDirectory() {
            return {
                map: null,
                markers: [],
                markerMap: {},
                infoWindow: null,
                highlightedTrainerId: null,

                init() {
                    this.loadGoogleMaps();
                },

                loadGoogleMaps() {
                    if (window.google && window.google.maps) {
                        this.initMap();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = `https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&callback=initTrainerMap&libraries=marker`;
                    script.async = true;
                    script.defer = true;
                    document.head.appendChild(script);

                    window.initTrainerMap = () => this.initMap();
                },

                initMap() {
                    const mapEl = document.getElementById('trainer-map');
                    this.map = new google.maps.Map(mapEl, {
                        center: { lat: 39.8283, lng: -98.5795 },
                        zoom: 4,
                        mapTypeControl: false,
                        streetViewControl: false,
                    });

                    this.infoWindow = new google.maps.InfoWindow();

                    const trainers = @json($mapMarkers);
                    const bounds = new google.maps.LatLngBounds();

                    trainers.forEach(trainer => {
                        const position = { lat: trainer.lat, lng: trainer.lng };
                        const marker = new google.maps.Marker({
                            position: position,
                            map: this.map,
                            title: trainer.name,
                        });

                        marker.addListener('click', () => {
                            this.infoWindow.setContent(`
                                <div style="min-width: 150px;">
                                    <p style="font-weight: 600; margin: 0 0 4px;">${trainer.name}</p>
                                    ${trainer.location ? `<p style="color: #6b7280; font-size: 0.875rem; margin: 0 0 8px;">${trainer.location}</p>` : ''}
                                    <a href="${trainer.url}" style="color: #374269; font-size: 0.875rem; font-weight: 500; text-decoration: none;">View Profile &rarr;</a>
                                </div>
                            `);
                            this.infoWindow.open(this.map, marker);
                            this.highlightedTrainerId = trainer.id;
                        });

                        this.markerMap[trainer.id] = marker;
                        bounds.extend(position);
                    });

                    if (trainers.length > 0) {
                        this.map.fitBounds(bounds);
                        if (trainers.length === 1) {
                            this.map.setZoom(12);
                        }
                    }

                    // Load MarkerClusterer
                    const clustererScript = document.createElement('script');
                    clustererScript.src = 'https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js';
                    clustererScript.onload = () => {
                        new markerClusterer.MarkerClusterer({
                            map: this.map,
                            markers: Object.values(this.markerMap),
                        });
                    };
                    document.head.appendChild(clustererScript);
                },

                bounceMarker(trainerId) {
                    this.highlightedTrainerId = trainerId;
                    const marker = this.markerMap[trainerId];
                    if (marker) {
                        marker.setAnimation(google.maps.Animation.BOUNCE);
                        setTimeout(() => marker.setAnimation(null), 750);
                    }
                },
            };
        }
    </script>
    @else
    <script>
        function trainerDirectory() {
            return {
                highlightedTrainerId: null,
                init() {
                    document.getElementById('trainer-map').innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-sm">Map requires Google Maps API key configuration.</div>';
                },
                bounceMarker() {},
            };
        }
    </script>
    @endif
    @endpush
</x-public-layout>
