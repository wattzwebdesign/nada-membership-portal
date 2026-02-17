<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Training') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2" style="color: #374269;">New Training</h3>
                    <p class="text-sm text-gray-500 mb-6">Fill out the details below to create a new training. It will be submitted for admin approval before being published.</p>

                    <form method="POST" action="{{ route('trainer.trainings.store') }}" x-data="trainingForm()">
                        @csrf

                        <div class="space-y-6">
                            {{-- Title --}}
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., NADA ADS Training - Spring 2026">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="Describe the training content, objectives, and what attendees should expect...">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Type --}}
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700">Training Type *</label>
                                <select name="type" id="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" onchange="toggleLocationFields()">
                                    <option value="in_person" {{ old('type') === 'in_person' ? 'selected' : '' }}>In Person</option>
                                    <option value="virtual" {{ old('type') === 'virtual' ? 'selected' : '' }}>Virtual</option>
                                    <option value="hybrid" {{ old('type') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                </select>
                                @error('type')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Location Fields --}}
                            <div id="location-fields" class="space-y-4">
                                <div>
                                    <label for="location_name" class="block text-sm font-medium text-gray-700">Location Name</label>
                                    <input type="text" name="location_name" id="location_name" value="{{ old('location_name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., Community Health Center">
                                    @error('location_name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="location_address" class="block text-sm font-medium text-gray-700">Location Address</label>
                                    <input type="text" name="location_address" id="location_address" value="{{ old('location_address') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., 123 Main St, New York, NY 10001">
                                    @error('location_address')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Virtual Link --}}
                            <div id="virtual-field">
                                <label for="virtual_link" class="block text-sm font-medium text-gray-700">Virtual Meeting Link</label>
                                <input type="url" name="virtual_link" id="virtual_link" value="{{ old('virtual_link') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., https://zoom.us/j/123456789">
                                @error('virtual_link')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Date & Time --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date & Time *</label>
                                    <input type="text" name="start_date" id="start_date" value="{{ old('start_date') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" data-datepicker='{"enableTime":true,"time_24hr":false,"minuteIncrement":15,"altInput":true,"altFormat":"M j, Y h:i K","dateFormat":"Y-m-d H:i","minDate":"today"}'>
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

                            {{-- Timezone --}}
                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                                <select name="timezone" id="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                    <option value="America/New_York" {{ old('timezone', 'America/New_York') === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                    <option value="America/Chicago" {{ old('timezone') === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                    <option value="America/Denver" {{ old('timezone') === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                    <option value="America/Los_Angeles" {{ old('timezone') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                </select>
                            </div>

                            {{-- Max Attendees --}}
                            <div>
                                <label for="max_attendees" class="block text-sm font-medium text-gray-700">Max Attendees</label>
                                <p class="text-xs text-gray-500 mb-1">Leave blank for unlimited capacity.</p>
                                <input type="number" name="max_attendees" id="max_attendees" value="{{ old('max_attendees') }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., 30">
                                @error('max_attendees')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Group Training Toggle --}}
                            <div class="border border-gray-200 rounded-lg p-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_group" value="1" class="rounded border-gray-300 shadow-sm" style="color: #374269;" x-model="isGroup" {{ old('is_group') ? 'checked' : '' }}>
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
                                    <button type="button" @click="addInvitee()" class="mt-1 inline-flex items-center text-sm font-medium" style="color: #374269;">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Add Another Email
                                    </button>
                                    @error('invitees.*')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Paid Toggle & Price (hidden when group) --}}
                            <div x-show="!isGroup" class="border border-gray-200 rounded-lg p-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_paid" value="1" class="rounded border-gray-300 shadow-sm" style="color: #374269;" x-model="isPaid" {{ old('is_paid') ? 'checked' : '' }}>
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

                        {{-- Submit --}}
                        <div class="mt-6 flex items-center justify-between">
                            <a href="{{ route('trainer.trainings.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                            <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                Submit for Review
                            </button>
                        </div>
                    </form>
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

        function trainingForm() {
            const oldInvitees = @json(old('invitees', ['']));
            return {
                isGroup: {{ old('is_group') ? 'true' : 'false' }},
                isPaid: {{ old('is_paid') ? 'true' : 'false' }},
                invitees: oldInvitees.map(email => ({ email: email || '', status: '', message: '', name: '', checking: false })),
                addInvitee() {
                    this.invitees.push({ email: '', status: '', message: '', name: '', checking: false });
                },
                removeInvitee(index) {
                    this.invitees.splice(index, 1);
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
