<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Available Trainings') }}
            </h2>
            <div class="flex items-center gap-3">
                {{-- View Toggle --}}
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    <button id="btn-list"
                            onclick="switchView('list')"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium border border-gray-300 rounded-l-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        List
                    </button>
                    <button id="btn-calendar"
                            onclick="switchView('calendar')"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium border border-gray-300 border-l-0 rounded-r-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Calendar
                    </button>
                </div>

                <a data-guide="trainings-my-registrations" href="{{ route('trainings.my-registrations') }}" class="inline-flex items-center text-sm font-medium text-brand-secondary hover:underline">
                    My Registrations
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ==================== LIST VIEW ==================== --}}
            <div id="list-view">
                {{-- Filters --}}
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <form method="GET" action="{{ route('trainings.index') }}" class="flex flex-col sm:flex-row flex-wrap gap-3">
                            <input type="hidden" name="view" value="list">
                            <div class="flex-1 min-w-[180px]">
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search trainings..." class="w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 text-sm">
                            </div>
                            <div>
                                <select name="type" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All Types</option>
                                    <option value="in_person" {{ request('type') === 'in_person' ? 'selected' : '' }}>In Person</option>
                                    <option value="virtual" {{ request('type') === 'virtual' ? 'selected' : '' }}>Virtual</option>
                                    <option value="hybrid" {{ request('type') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                            </div>
                            <div>
                                <select name="price" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm text-sm">
                                    <option value="">All Prices</option>
                                    <option value="free" {{ request('price') === 'free' ? 'selected' : '' }}>Free</option>
                                    <option value="paid" {{ request('price') === 'paid' ? 'selected' : '' }}>Paid</option>
                                </select>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" name="date_from" value="{{ request('date_from') }}" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm text-sm" placeholder="From" data-datepicker='{"altInput":true,"altFormat":"M j, Y","dateFormat":"Y-m-d"}'>
                                <span class="text-gray-400 text-sm">to</span>
                                <input type="text" name="date_to" value="{{ request('date_to') }}" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm text-sm" placeholder="To" data-datepicker='{"altInput":true,"altFormat":"M j, Y","dateFormat":"Y-m-d"}'>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                Filter
                            </button>
                            @if(request()->hasAny(['search', 'type', 'price', 'date_from', 'date_to']))
                                <a href="{{ route('trainings.index', ['view' => 'list']) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- Training Grid --}}
                @if ($trainings->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($trainings as $training)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                                <div class="p-6">
                                    {{-- Type Badge --}}
                                    <div class="flex items-center justify-between mb-3">
                                        @php
                                            $typeBadgeColors = [
                                                'in_person' => 'bg-blue-100 text-blue-800',
                                                'virtual' => 'bg-purple-100 text-purple-800',
                                                'hybrid' => 'bg-indigo-100 text-indigo-800',
                                            ];
                                            $typeBadgeColor = $typeBadgeColors[$training->type->value] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $training->type->value)) }}
                                        </span>
                                        @if ($training->is_paid)
                                            <span class="text-lg font-bold text-brand-secondary">${{ number_format($training->price_cents / 100, 2) }}</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Free</span>
                                        @endif
                                    </div>

                                    {{-- Title --}}
                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">
                                        <a href="{{ route('trainings.show', $training) }}" class="hover:underline">{{ $training->title }}</a>
                                    </h4>

                                    {{-- Date & Time --}}
                                    <div class="flex items-center text-sm text-gray-500 mb-2">
                                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ $training->start_date->format('M j, Y \a\t g:i A') }}
                                    </div>

                                    {{-- Location --}}
                                    @if ($training->type !== App\Enums\TrainingType::Virtual && $training->location_name)
                                        <div class="flex items-center text-sm text-gray-500 mb-2">
                                            <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ $training->location_name }}
                                        </div>
                                    @endif

                                    {{-- Trainer --}}
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        {{ $training->trainer->full_name ?? 'N/A' }}
                                    </div>

                                    {{-- Spots --}}
                                    @if ($training->max_attendees)
                                        @php
                                            $spotsLeft = $training->max_attendees - ($training->registrations_count ?? 0);
                                        @endphp
                                        <div class="mb-4">
                                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                                <span>{{ $training->registrations_count ?? 0 }} registered</span>
                                                <span>{{ $spotsLeft }} spots left</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full {{ $spotsLeft <= 5 ? 'bg-red-500' : 'bg-brand-primary' }}" style="width: {{ min(100, (($training->registrations_count ?? 0) / $training->max_attendees) * 100) }}%;"></div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Register Button --}}
                                    <a href="{{ route('trainings.show', $training) }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white transition bg-brand-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($trainings->hasPages())
                        <div class="mt-8">
                            {{ $trainings->withQueryString()->links() }}
                        </div>
                    @endif
                @else
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Trainings Available</h3>
                            <p class="mt-1 text-sm text-gray-500">There are no published trainings at this time. Check back later.</p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ==================== CALENDAR VIEW ==================== --}}
            <div id="calendar-view" style="display: none;">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div id="calendar"></div>
                </div>

                {{-- Legend --}}
                <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded" style="background-color: #3B82F6;"></span> In Person
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded" style="background-color: #8B5CF6;"></span> Virtual
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded" style="background-color: #6366F1;"></span> Hybrid
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
    (function() {
        const eventsUrl = '{{ url('/trainings/calendar-events') }}';
        const initialView = new URLSearchParams(window.location.search).get('view') || 'list';

        let calendar = null;

        // Toggle button styling
        function updateToggleButtons(activeView) {
            const btnList = document.getElementById('btn-list');
            const btnCal = document.getElementById('btn-calendar');
            const activeClasses = ['bg-brand-primary', 'text-white'];
            const inactiveClasses = ['bg-white', 'text-gray-700', 'hover:bg-gray-50'];

            if (activeView === 'list') {
                btnList.classList.remove(...inactiveClasses);
                btnList.classList.add(...activeClasses);
                btnCal.classList.remove(...activeClasses);
                btnCal.classList.add(...inactiveClasses);
            } else {
                btnCal.classList.remove(...inactiveClasses);
                btnCal.classList.add(...activeClasses);
                btnList.classList.remove(...activeClasses);
                btnList.classList.add(...inactiveClasses);
            }
        }

        // Switch between views
        window.switchView = function(view) {
            const listView = document.getElementById('list-view');
            const calendarView = document.getElementById('calendar-view');

            if (view === 'calendar') {
                listView.style.display = 'none';
                calendarView.style.display = 'block';
                if (!calendar) {
                    initCalendar();
                } else {
                    calendar.updateSize();
                }
            } else {
                listView.style.display = 'block';
                calendarView.style.display = 'none';
            }

            updateToggleButtons(view);

            const url = new URL(window.location);
            url.searchParams.set('view', view);
            history.replaceState({}, '', url);
        };

        // Initialize FullCalendar
        function initCalendar() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                height: 'auto',
                navLinks: true,
                editable: false,
                dayMaxEvents: true,
                events: function(info, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        start: info.startStr,
                        end: info.endStr,
                    });
                    fetch(eventsUrl + '?' + params, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                    })
                    .then(function(resp) { return resp.json(); })
                    .then(function(events) { successCallback(events); })
                    .catch(function(err) { failureCallback(err); });
                },
                eventClick: function(info) {
                    info.jsEvent.preventDefault();
                    if (info.event.url) {
                        window.location.href = info.event.url;
                    }
                },
                eventDidMount: function(info) {
                    var props = info.event.extendedProps;
                    var type = props.type ? props.type.replace('_', ' ') : '';
                    type = type.charAt(0).toUpperCase() + type.slice(1);
                    var tip = info.event.title;
                    if (props.trainer) tip += '\nTrainer: ' + props.trainer;
                    if (type) tip += '\nType: ' + type;
                    if (props.price) tip += '\nPrice: ' + props.price;
                    if (props.location) tip += '\nLocation: ' + props.location;
                    info.el.setAttribute('title', tip);
                },
            });
            calendar.render();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            switchView(initialView);
        });
    })();
    </script>
    <style>
        /* FullCalendar event styling */
        .fc-event {
            border-radius: 4px !important;
            font-size: 0.8rem !important;
            padding: 2px 4px !important;
            cursor: pointer !important;
        }
        .fc-daygrid-event-dot {
            display: none !important;
        }
        .fc-event:hover {
            opacity: 0.85;
        }
        .fc .fc-button-primary {
            background-color: #1C3519 !important;
            border-color: #1C3519 !important;
        }
        .fc .fc-button-primary:not(:disabled).fc-button-active,
        .fc .fc-button-primary:not(:disabled):active {
            background-color: #2E522A !important;
            border-color: #2E522A !important;
        }
        .fc .fc-button-primary:hover {
            background-color: #2E522A !important;
            border-color: #2E522A !important;
        }
        .fc .fc-today-button:disabled {
            opacity: 0.5;
        }
        .fc .fc-day-today {
            background-color: rgba(173, 126, 7, 0.08) !important;
        }
    </style>
    @endpush
</x-app-layout>
