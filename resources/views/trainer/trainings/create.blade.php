<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Training') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    <p class="text-sm font-medium">Please fix the following errors:</p>
                    <ul class="mt-1 text-sm list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('trainer.trainings.store') }}" x-data="trainingForm()">
                @csrf

                @if(isset($fromRequest) && $fromRequest)
                    <input type="hidden" name="group_training_request_id" value="{{ $fromRequest->id }}">
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Left Column (2/3) --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Create from Group Request --}}
                        @if(isset($availableRequests) && $availableRequests->count() > 0)
                            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-brand-secondary">
                                <label for="from_request_select" class="block text-sm font-medium text-gray-700 mb-1">Create from Group Request</label>
                                <p class="text-xs text-gray-500 mb-2">Select a paid group training request to pre-fill the form.</p>
                                <select id="from_request_select" onchange="if(this.value) window.location='{{ route('trainer.trainings.create') }}?from_request=' + this.value; else window.location='{{ route('trainer.trainings.create') }}';" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    <option value="">-- None (blank form) --</option>
                                    @foreach($availableRequests as $req)
                                        <option value="{{ $req->id }}" {{ (isset($fromRequest) && $fromRequest && $fromRequest->id === $req->id) ? 'selected' : '' }}>
                                            {{ $req->training_name }} &mdash; {{ $req->company_full_name }} ({{ $req->training_date->format('M j, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Training Details --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-1 text-brand-primary">Training Details</h3>
                            <p class="text-sm text-gray-500 mb-5">Fill out the details below. It will be submitted for admin approval before being published.</p>

                            <div class="space-y-5">
                                {{-- Title --}}
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                                    <input type="text" name="title" id="title" value="{{ old('title', isset($fromRequest) && $fromRequest ? $fromRequest->training_name : '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., NADA ADS Training - Spring 2026">
                                    @error('title')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea name="description" id="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Describe the training content, objectives, and what attendees should expect...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Location</h3>

                            <div class="space-y-5">
                                <div id="location-fields" class="space-y-5">
                                    <div>
                                        <label for="location_name" class="block text-sm font-medium text-gray-700">Location Name</label>
                                        <input type="text" name="location_name" id="location_name" value="{{ old('location_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., Community Health Center">
                                        @error('location_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="location_address" class="block text-sm font-medium text-gray-700">Location Address</label>
                                        <input type="text" name="location_address" id="location_address" value="{{ old('location_address', isset($fromRequest) && $fromRequest ? $fromRequest->training_city . ', ' . $fromRequest->training_state : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., 123 Main St, New York, NY 10001">
                                        @error('location_address')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Group Training --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_group" value="1" class="rounded border-gray-300 shadow-sm text-brand-primary" x-model="isGroup" {{ old('is_group', isset($fromRequest) && $fromRequest ? '1' : '') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700">Group Training (Invite Only)</span>
                                </label>
                                <p class="mt-1 ml-6 text-xs text-gray-500">Group trainings are always free and only visible to invited members.</p>

                                {{-- Invitee Email Repeater --}}
                                <div x-show="isGroup" x-cloak class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Invitee Emails</label>
                                    <template x-for="(invitee, index) in invitees" :key="index">
                                        <div class="mb-2">
                                            <div class="flex items-center gap-2">
                                                <input type="email" :name="'invitees[' + index + ']'" x-model="invitee.email" @blur="checkEmail(index)" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="email@example.com">
                                                <button type="button" @click="removeInvitee(index)" class="flex-shrink-0 text-red-500 hover:text-red-700" x-show="invitees.length > 1">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                            {{-- Membership status indicator --}}
                                            <div class="mt-1 ml-1 text-xs flex items-center gap-1" x-show="invitee.checking || invitee.status" x-cloak>
                                                <template x-if="invitee.checking">
                                                    <span class="text-gray-400 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                        Checking...
                                                    </span>
                                                </template>
                                                <template x-if="!invitee.checking && invitee.status === 'active'">
                                                    <span class="text-green-600 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        <span x-text="invitee.name"></span> &mdash; Active member
                                                    </span>
                                                </template>
                                                <template x-if="!invitee.checking && invitee.status === 'no_membership'">
                                                    <span class="text-yellow-600 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                                        <span x-text="invitee.name"></span> &mdash; No active membership
                                                    </span>
                                                </template>
                                                <template x-if="!invitee.checking && invitee.status === 'not_found'">
                                                    <span class="text-red-500 flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        No account found with this email
                                                    </span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" @click="addInvitee()" class="mt-1 inline-flex items-center text-sm font-medium text-brand-primary">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add Another Email
                                    </button>
                                    @error('invitees.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Paid Toggle & Price (hidden when group) --}}
                            <div x-show="!isGroup" class="border border-gray-200 rounded-lg p-4 mt-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_paid" value="1" class="rounded border-gray-300 shadow-sm text-brand-primary" x-model="isPaid" {{ old('is_paid') ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm font-medium text-gray-700">This is a paid training</span>
                                </label>
                                <div x-show="isPaid" x-cloak class="mt-4">
                                    <label for="price" class="block text-sm font-medium text-gray-700">Price ($) *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input type="number" name="price" id="price" value="{{ old('price') }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="0.00">
                                    </div>
                                    @error('price')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column (1/3) --}}
                    <div class="lg:col-span-1 space-y-6">

                        {{-- Training Type --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Training Type</h3>

                            <div>
                                @php $defaultType = old('type', isset($fromRequest) && $fromRequest ? 'in_person' : ''); @endphp
                                <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                                <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" onchange="toggleLocationFields()">
                                    <option value="in_person" {{ $defaultType === 'in_person' || !$defaultType ? 'selected' : '' }}>In Person</option>
                                    <option value="virtual" {{ $defaultType === 'virtual' ? 'selected' : '' }}>Virtual</option>
                                    <option value="hybrid" {{ $defaultType === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Date & Time --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Date & Time</h3>

                            <div class="space-y-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date & Time *</label>
                                    <input type="text" name="start_date" id="start_date" value="{{ old('start_date', isset($fromRequest) && $fromRequest ? $fromRequest->training_date->format('Y-m-d') . ' 09:00' : '') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" data-datepicker='{"enableTime":true,"time_24hr":false,"minuteIncrement":15,"altInput":true,"altFormat":"M j, Y h:i K","dateFormat":"Y-m-d H:i","minDate":"today"}'>
                                    @error('start_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date & Time *</label>
                                    <input type="text" name="end_date" id="end_date" value="{{ old('end_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" data-datepicker='{"enableTime":true,"time_24hr":false,"minuteIncrement":15,"altInput":true,"altFormat":"M j, Y h:i K","dateFormat":"Y-m-d H:i","minDate":"today"}'>
                                    @error('end_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Virtual Meeting Link --}}
                        <div id="virtual-field" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Meeting Link</h3>

                            <div>
                                <label for="virtual_link" class="block text-sm font-medium text-gray-700">Virtual Meeting Link</label>
                                <input type="url" name="virtual_link" id="virtual_link" value="{{ old('virtual_link') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="https://zoom.us/j/123456789">
                                <p class="mt-1 text-xs text-gray-400">Required for virtual and hybrid trainings.</p>
                                @error('virtual_link')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Timezone --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Timezone</h3>

                            <div>
                                <select name="timezone" id="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    <option value="America/New_York" {{ old('timezone', 'America/New_York') === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ old('timezone') === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ old('timezone') === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ old('timezone') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                </select>
                            </div>
                        </div>

                        {{-- Max Attendees --}}
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Capacity</h3>

                            <div>
                                <label for="max_attendees" class="block text-sm font-medium text-gray-700">Max Attendees</label>
                                <input type="number" name="max_attendees" id="max_attendees" value="{{ old('max_attendees', isset($fromRequest) && $fromRequest ? $fromRequest->number_of_tickets : '') }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., 30">
                                <p class="mt-1 text-xs text-gray-400">Leave blank for unlimited capacity.</p>
                                @error('max_attendees')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-6 flex items-center justify-between">
                    <a href="{{ route('trainer.trainings.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                    <div class="flex items-center gap-3">
                        <p x-show="isGroup && !canSubmit" x-cloak class="text-sm text-red-600">All invitees must be active members</p>
                        <button type="submit" :disabled="!canSubmit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white disabled:opacity-50 disabled:cursor-not-allowed bg-brand-primary">
                            Submit for Review
                        </button>
                    </div>
                </div>
            </form>
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

        function trainingForm() {
            @php
                $prefillEmails = [];
                if (isset($fromRequest) && $fromRequest && !old('invitees')) {
                    $prefillEmails = $fromRequest->members->pluck('email')->toArray();
                }
            @endphp
            const oldInvitees = @json(old('invitees', !empty($prefillEmails) ? $prefillEmails : ['']));
            return {
                isGroup: {{ old('is_group', isset($fromRequest) && $fromRequest ? '1' : '') ? 'true' : 'false' }},
                isPaid: {{ old('is_paid') ? 'true' : 'false' }},
                invitees: oldInvitees.map(email => ({ email: email || '', status: '', message: '', name: '', checking: false })),
                init() {
                    // Auto-check membership status for pre-filled invitees
                    this.invitees.forEach((invitee, index) => {
                        if (invitee.email && invitee.email.includes('@')) {
                            this.checkEmail(index);
                        }
                    });
                },
                addInvitee() {
                    this.invitees.push({ email: '', status: '', message: '', name: '', checking: false });
                },
                removeInvitee(index) {
                    this.invitees.splice(index, 1);
                },
                get canSubmit() {
                    if (!this.isGroup) return true;
                    const filled = this.invitees.filter(i => (i.email || '').trim());
                    if (filled.length === 0) return true;
                    return filled.every(i => i.status === 'active') && !filled.some(i => i.checking);
                },
                async checkEmail(index) {
                    const invitee = this.invitees[index];
                    const email = (invitee.email || '').trim();
                    if (!email || !email.includes('@')) {
                        invitee.status = '';
                        invitee.message = '';
                        invitee.name = '';
                        return;
                    }
                    invitee.checking = true;
                    invitee.status = '';
                    try {
                        const res = await fetch('{{ route("trainer.invitee.check") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ email })
                        });
                        const data = await res.json();
                        invitee.status = data.status;
                        invitee.message = data.message || '';
                        invitee.name = data.name || '';
                    } catch (e) {
                        invitee.status = '';
                    } finally {
                        invitee.checking = false;
                    }
                }
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleLocationFields();
        });
    </script>
</x-app-layout>
