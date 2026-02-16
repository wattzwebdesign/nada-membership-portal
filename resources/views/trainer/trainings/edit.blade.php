<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Training') }}: {{ $training->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Status Bar --}}
                    @php
                        $statusValue = is_object($training->status) ? $training->status->value : $training->status;
                    @endphp
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold" style="color: #374269;">Training Details</h3>
                        @php
                            $statusBadgeColors = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'published' => 'bg-green-100 text-green-800',
                                'canceled' => 'bg-red-100 text-red-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                            ];
                            $statusBadgeColor = $statusBadgeColors[$statusValue] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadgeColor }}">
                            {{ ucfirst($statusValue) }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('trainer.trainings.update', $training) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            {{-- Title --}}
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                                <input type="text" name="title" id="title" value="{{ old('title', $training->title) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">{{ old('description', $training->description) }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Training Type *</label>
                                @php
                                    $typeValue = is_object($training->type) ? $training->type->value : $training->type;
                                @endphp
                                <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" onchange="toggleLocationFields()">
                                    <option value="in_person" {{ old('type', $typeValue) === 'in_person' ? 'selected' : '' }}>In Person</option>
                                    <option value="virtual" {{ old('type', $typeValue) === 'virtual' ? 'selected' : '' }}>Virtual</option>
                                    <option value="hybrid" {{ old('type', $typeValue) === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                            </div>

                            {{-- Location Fields --}}
                            <div id="location-fields" class="space-y-4">
                                <div>
                                    <label for="location_name" class="block text-sm font-medium text-gray-700">Location Name</label>
                                    <input type="text" name="location_name" id="location_name" value="{{ old('location_name', $training->location_name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                </div>
                                <div>
                                    <label for="location_address" class="block text-sm font-medium text-gray-700">Location Address</label>
                                    <input type="text" name="location_address" id="location_address" value="{{ old('location_address', $training->location_address) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                </div>
                            </div>

                            {{-- Virtual Link --}}
                            <div id="virtual-field">
                                <label for="virtual_link" class="block text-sm font-medium text-gray-700">Virtual Meeting Link</label>
                                <input type="url" name="virtual_link" id="virtual_link" value="{{ old('virtual_link', $training->virtual_link) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                            </div>

                            {{-- Date & Time --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date & Time *</label>
                                    <input type="text" name="start_date" id="start_date" value="{{ old('start_date', $training->start_date->format('Y-m-d H:i')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" data-datepicker='{"enableTime":true,"time_24hr":false,"minuteIncrement":15,"altInput":true,"altFormat":"M j, Y h:i K","dateFormat":"Y-m-d H:i"}'>
                                </div>
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date & Time *</label>
                                    <input type="text" name="end_date" id="end_date" value="{{ old('end_date', $training->end_date->format('Y-m-d H:i')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" data-datepicker='{"enableTime":true,"time_24hr":false,"minuteIncrement":15,"altInput":true,"altFormat":"M j, Y h:i K","dateFormat":"Y-m-d H:i"}'>
                                </div>
                            </div>

                            {{-- Max Attendees --}}
                            <div>
                                <label for="max_attendees" class="block text-sm font-medium text-gray-700">Max Attendees</label>
                                <input type="number" name="max_attendees" id="max_attendees" value="{{ old('max_attendees', $training->max_attendees) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                            </div>

                            {{-- Paid Toggle & Price --}}
                            <div class="border border-gray-200 rounded-lg p-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_paid" id="is_paid" value="1" class="rounded border-gray-300 shadow-sm" style="color: #374269;" {{ old('is_paid', $training->is_paid) ? 'checked' : '' }} onchange="togglePriceField()">
                                    <span class="ml-2 text-sm font-medium text-gray-700">This is a paid training</span>
                                </label>
                                <div id="price-field" class="mt-4 {{ old('is_paid', $training->is_paid) ? '' : 'hidden' }}">
                                    <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="price" id="price" value="{{ old('price', $training->price_cents / 100) }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                            <a href="{{ route('trainer.trainings.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Back to Trainings</a>
                            <div class="flex flex-wrap gap-3">
                                @if ($statusValue === 'draft')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                        Save Changes
                                    </button>
                                @elseif ($statusValue === 'published')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                        Update Training
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>

                    {{-- Extra Actions --}}
                    @if (in_array($statusValue, ['draft', 'published']))
                        <div class="mt-6 border-t border-gray-200 pt-6 flex flex-wrap gap-3">
                            @if ($statusValue === 'draft')
                                <form method="POST" action="{{ route('trainer.trainings.publish', $training) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Publish Training
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('trainer.trainings.cancel', $training) }}" onsubmit="return confirm('Are you sure you want to cancel this training? All registered attendees will be notified.');">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50">
                                    Cancel Training
                                </button>
                            </form>

                            <a href="{{ route('trainer.attendees.index', $training) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                                View Attendees ({{ $training->registrations_count ?? 0 }})
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleLocationFields() {
            const type = document.getElementById('type').value;
            const locationFields = document.getElementById('location-fields');
            const virtualField = document.getElementById('virtual-field');

            locationFields.style.display = (type === 'virtual') ? 'none' : 'block';
            virtualField.style.display = (type === 'in_person') ? 'none' : 'block';
        }

        function togglePriceField() {
            const isPaid = document.getElementById('is_paid').checked;
            const priceField = document.getElementById('price-field');
            priceField.classList.toggle('hidden', !isPaid);
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleLocationFields();
        });
    </script>
</x-app-layout>
