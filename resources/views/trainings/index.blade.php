<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Available Trainings') }}
            </h2>
            <div class="flex items-center gap-3">
                {{-- View Toggle --}}
                <div x-data class="inline-flex rounded-md shadow-sm" role="group">
                    <button @click="$dispatch('set-view', 'list')"
                            :class="($store.trainingView.current === 'list') ? 'bg-brand-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium border border-gray-300 rounded-l-md transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        List
                    </button>
                    <button @click="$dispatch('set-view', 'calendar')"
                            :class="($store.trainingView.current === 'calendar') ? 'bg-brand-primary text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
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

    <div class="py-8" x-data="trainingCalendar('{{ request('view', 'list') }}', '{{ route('trainings.calendar-events') }}')" @set-view.window="setView($event.detail)">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ==================== LIST VIEW ==================== --}}
            <div x-show="view === 'list'" x-cloak>
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
            <div x-show="view === 'calendar'" x-cloak>
                {{-- Month Navigation --}}
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 flex items-center justify-between">
                        <button @click="prevMonth()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            Prev
                        </button>
                        <h3 class="text-lg font-semibold text-gray-800" x-text="monthLabel"></h3>
                        <button @click="nextMonth()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Loading Spinner --}}
                <div x-show="loading" class="flex justify-center py-12">
                    <svg class="animate-spin h-8 w-8 text-brand-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                {{-- Calendar Grid --}}
                <div x-show="!loading" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    {{-- Day-of-week Headers --}}
                    <div class="grid grid-cols-7 border-b border-gray-200">
                        <template x-for="dayName in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="dayName">
                            <div class="px-2 py-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="dayName"></div>
                        </template>
                    </div>

                    {{-- Day Cells --}}
                    <div class="grid grid-cols-7">
                        <template x-for="(day, index) in calendarDays" :key="index">
                            <div class="min-h-[100px] border-r border-b border-gray-100 px-1 py-1"
                                 :class="day.isCurrentMonth ? 'bg-white' : 'bg-gray-50'">
                                {{-- Day Number --}}
                                <div class="text-xs font-medium mb-1 px-1"
                                     :class="{
                                         'text-gray-400': !day.isCurrentMonth,
                                         'text-gray-700': day.isCurrentMonth && !day.isToday,
                                         'text-white bg-brand-primary rounded-full w-6 h-6 flex items-center justify-center': day.isToday
                                     }"
                                     x-text="day.dayNumber"></div>

                                {{-- Events --}}
                                <div class="space-y-0.5">
                                    <template x-for="(evt, ei) in day.visibleEvents" :key="evt.id + '-' + index">
                                        <a :href="evt.url"
                                           class="block rounded px-1.5 py-0.5 text-xs font-medium truncate leading-tight cursor-pointer hover:opacity-80 transition-opacity"
                                           :class="eventColorClass(evt.type)"
                                           :title="evt.title + ' (' + evt.trainer + ') ' + evt.price">
                                            <span x-show="evt.showTitle" x-text="evt.title"></span>
                                            <span x-show="!evt.showTitle">&nbsp;</span>
                                        </a>
                                    </template>
                                    <template x-if="day.extraCount > 0">
                                        <div class="text-xs text-gray-500 px-1 font-medium" x-text="'+' + day.extraCount + ' more'"></div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="mt-4 flex flex-wrap gap-4 text-xs text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded bg-blue-500"></span> In Person
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded bg-purple-500"></span> Virtual
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded bg-indigo-500"></span> Hybrid
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('trainingView', {
            current: new URLSearchParams(window.location.search).get('view') || 'list',
        });
    });

    function trainingCalendar(initialView, eventsUrl) {
        const now = new Date();
        return {
            view: initialView || 'list',
            currentYear: now.getFullYear(),
            currentMonth: now.getMonth(),
            events: [],
            calendarDays: [],
            loading: false,
            maxVisibleEvents: 3,

            get monthLabel() {
                const date = new Date(this.currentYear, this.currentMonth, 1);
                return date.toLocaleString('default', { month: 'long', year: 'numeric' });
            },

            init() {
                Alpine.store('trainingView').current = this.view;
                if (this.view === 'calendar') {
                    this.fetchEvents();
                }
            },

            setView(newView) {
                this.view = newView;
                Alpine.store('trainingView').current = newView;
                const url = new URL(window.location);
                url.searchParams.set('view', newView);
                history.replaceState({}, '', url);
                if (newView === 'calendar' && this.calendarDays.length === 0) {
                    this.fetchEvents();
                }
            },

            prevMonth() {
                if (this.currentMonth === 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                } else {
                    this.currentMonth--;
                }
                this.fetchEvents();
            },

            nextMonth() {
                if (this.currentMonth === 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                } else {
                    this.currentMonth++;
                }
                this.fetchEvents();
            },

            async fetchEvents() {
                this.loading = true;
                const monthStr = this.currentYear + '-' + String(this.currentMonth + 1).padStart(2, '0');
                try {
                    const resp = await fetch(eventsUrl + '?month=' + monthStr, {
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' },
                    });
                    this.events = await resp.json();
                } catch (e) {
                    this.events = [];
                }
                this.buildCalendar();
                this.loading = false;
            },

            buildCalendar() {
                const year = this.currentYear;
                const month = this.currentMonth;
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const startDow = firstDay.getDay(); // 0=Sun
                const daysInMonth = lastDay.getDate();
                const today = new Date();
                const todayStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');

                const days = [];

                // Previous month padding
                const prevLastDay = new Date(year, month, 0);
                const prevDays = prevLastDay.getDate();
                for (let i = startDow - 1; i >= 0; i--) {
                    const d = prevDays - i;
                    const dateStr = this.dateStr(year, month - 1, d);
                    days.push(this.makeDayObj(d, dateStr, false, dateStr === todayStr));
                }

                // Current month
                for (let d = 1; d <= daysInMonth; d++) {
                    const dateStr = this.dateStr(year, month, d);
                    days.push(this.makeDayObj(d, dateStr, true, dateStr === todayStr));
                }

                // Next month padding
                const remaining = 7 - (days.length % 7);
                if (remaining < 7) {
                    for (let d = 1; d <= remaining; d++) {
                        const dateStr = this.dateStr(year, month + 1, d);
                        days.push(this.makeDayObj(d, dateStr, false, dateStr === todayStr));
                    }
                }

                this.calendarDays = days;
            },

            dateStr(year, month, day) {
                // Handle month overflow/underflow
                const d = new Date(year, month, day);
                return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            },

            makeDayObj(dayNumber, dateStr, isCurrentMonth, isToday) {
                const dayEvents = this.events
                    .filter(e => dateStr >= e.start && dateStr <= e.end)
                    .map(e => {
                        const dayOfWeek = new Date(dateStr).getDay();
                        const isStartDay = dateStr === e.start;
                        const isRowStart = dayOfWeek === 0; // Sunday
                        const isFirstOfMonth = dateStr.endsWith('-01');
                        return {
                            ...e,
                            showTitle: isStartDay || isRowStart || isFirstOfMonth,
                        };
                    });

                const maxVisible = this.maxVisibleEvents;
                return {
                    dayNumber,
                    dateStr,
                    isCurrentMonth,
                    isToday,
                    visibleEvents: dayEvents.slice(0, maxVisible),
                    extraCount: Math.max(0, dayEvents.length - maxVisible),
                };
            },

            eventColorClass(type) {
                const colors = {
                    in_person: 'bg-blue-100 text-blue-800 border-l-2 border-blue-500',
                    virtual: 'bg-purple-100 text-purple-800 border-l-2 border-purple-500',
                    hybrid: 'bg-indigo-100 text-indigo-800 border-l-2 border-indigo-500',
                };
                return colors[type] || 'bg-gray-100 text-gray-800 border-l-2 border-gray-500';
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
