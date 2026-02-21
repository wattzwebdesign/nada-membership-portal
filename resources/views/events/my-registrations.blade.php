<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Event Registrations</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @forelse ($registrations as $registration)
                        <div class="border-b last:border-b-0 py-4 first:pt-0 last:pb-0">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        {{ $registration->event->title }}
                                    </h3>
                                    <div class="mt-1 flex flex-wrap gap-3 text-sm text-gray-500">
                                        <span>{{ $registration->event->date_display }}</span>
                                        <span>{{ $registration->event->location_display }}</span>
                                        <span>Reg #: {{ $registration->registration_number }}</span>
                                    </div>
                                    <div class="mt-2 flex gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $registration->status->color() === 'info' ? 'bg-blue-100 text-blue-800' : ($registration->status->color() === 'success' ? 'bg-green-100 text-green-800' : ($registration->status->color() === 'danger' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ $registration->status->label() }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $registration->payment_status->color() === 'success' ? 'bg-green-100 text-green-800' : ($registration->payment_status->color() === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $registration->payment_status->label() }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <a href="{{ route('events.confirmation', ['event' => $registration->event->slug, 'registration' => $registration->id]) }}"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        View Details
                                    </a>

                                    @if ($registration->status->value === 'registered' && $registration->event->start_date->isFuture())
                                        <form method="POST" action="{{ route('events.cancel', $registration->event->slug) }}"
                                            onsubmit="return confirm('Are you sure you want to cancel this registration?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <p class="text-gray-500">You haven't registered for any events yet.</p>
                            <a href="{{ route('public.events.index') }}" class="mt-2 inline-block text-brand-primary hover:underline">Browse Events</a>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4">
                {{ $registrations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
