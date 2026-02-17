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

            @php
                $statusValue = is_object($training->status) ? $training->status->value : $training->status;
                $isEditable = in_array($statusValue, ['pending_approval', 'denied']);
            @endphp

            {{-- Denied Reason Alert --}}
            @if ($statusValue === 'denied' && $training->denied_reason)
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <div>
                            <h4 class="text-sm font-semibold text-red-800">Training Denied</h4>
                            <p class="mt-1 text-sm text-red-700">{{ $training->denied_reason }}</p>
                            <p class="mt-2 text-xs text-red-600">You may edit your training and resubmit it for review using the button below.</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Pending Approval Info --}}
            @if ($statusValue === 'pending_approval')
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <h4 class="text-sm font-semibold text-yellow-800">Pending Review</h4>
                            <p class="mt-1 text-sm text-yellow-700">This training is awaiting admin approval. You will be notified once it has been reviewed.</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    {{-- Status Bar --}}
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold" style="color: #374269;">Training Details</h3>
                        @php
                            $statusBadgeColors = [
                                'pending_approval' => 'bg-yellow-100 text-yellow-800',
                                'published' => 'bg-green-100 text-green-800',
                                'denied' => 'bg-red-100 text-red-800',
                                'canceled' => 'bg-red-100 text-red-800',
                                'completed' => 'bg-blue-100 text-blue-800',
                            ];
                            $statusBadgeColor = $statusBadgeColors[$statusValue] ?? 'bg-gray-100 text-gray-800';
                            $statusLabels = [
                                'pending_approval' => 'Pending Approval',
                                'published' => 'Published',
                                'denied' => 'Denied',
                                'canceled' => 'Canceled',
                                'completed' => 'Completed',
                            ];
                            $statusLabel = $statusLabels[$statusValue] ?? ucfirst($statusValue);
                        @endphp
                        <div class="flex items-center gap-2">
                            @if ($training->is_group)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    Group Training
                                </span>
                            @endif
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadgeColor }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('trainer.trainings.update', $training) }}" x-data="{ isPaid: {{ old('is_paid', $training->is_paid) ? 'true' : 'false' }} }">
                        @csrf
                        @method('PUT')

                        <fieldset {{ !$isEditable ? 'disabled' : '' }}>
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

                                {{-- Timezone --}}
                                <div>
                                    <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                                    <select name="timezone" id="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                        <option value="America/New_York" {{ old('timezone', $training->timezone ?? 'America/New_York') === 'America/New_York' ? 'selected' : '' }}>Eastern Time</option>
                                        <option value="America/Chicago" {{ old('timezone', $training->timezone) === 'America/Chicago' ? 'selected' : '' }}>Central Time</option>
                                        <option value="America/Denver" {{ old('timezone', $training->timezone) === 'America/Denver' ? 'selected' : '' }}>Mountain Time</option>
                                        <option value="America/Los_Angeles" {{ old('timezone', $training->timezone) === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time</option>
                                    </select>
                                </div>

                                {{-- Max Attendees --}}
                                <div>
                                    <label for="max_attendees" class="block text-sm font-medium text-gray-700">Max Attendees</label>
                                    <input type="number" name="max_attendees" id="max_attendees" value="{{ old('max_attendees', $training->max_attendees) }}" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                </div>

                                {{-- Paid Toggle & Price (not shown for group trainings) --}}
                                @if (!$training->is_group)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <label class="flex items-center cursor-pointer">
                                            <input type="checkbox" name="is_paid" value="1" class="rounded border-gray-300 shadow-sm" style="color: #374269;" x-model="isPaid" {{ old('is_paid', $training->is_paid) ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm font-medium text-gray-700">This is a paid training</span>
                                        </label>
                                        <div x-show="isPaid" x-cloak class="mt-4">
                                            <label for="price" class="block text-sm font-medium text-gray-700">Price ($)</label>
                                            <div class="mt-1 relative rounded-md shadow-sm">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">$</span>
                                                </div>
                                                <input type="number" name="price" id="price" value="{{ old('price', $training->price_cents ? $training->price_cents / 100 : '') }}" step="0.01" min="0" class="pl-7 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm">
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Group Training Invitees --}}
                                @if ($training->is_group)
                                    <div class="border border-gray-200 rounded-lg p-4" x-data="inviteeRepeater()">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">
                                            <span class="inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                Invitee Emails
                                            </span>
                                        </h4>
                                        <p class="text-xs text-gray-500 mb-3">Group trainings are always free. Invitations will be sent after admin approval.</p>
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
                                @endif
                            </div>
                        </fieldset>

                        {{-- Actions --}}
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                            <a href="{{ route('trainer.trainings.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Back to Trainings</a>
                            <div class="flex flex-wrap gap-3">
                                @if ($statusValue === 'pending_approval')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                        Save Changes
                                    </button>
                                @elseif ($statusValue === 'denied')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        Resubmit for Review
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>

                    {{-- Extra Actions --}}
                    @if ($statusValue === 'published')
                        <div class="mt-6 border-t border-gray-200 pt-6 flex flex-wrap gap-3">
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
                    @elseif (in_array($statusValue, ['pending_approval', 'denied']))
                        <div class="mt-6 border-t border-gray-200 pt-6 flex flex-wrap gap-3">
                            <form method="POST" action="{{ route('trainer.trainings.destroy', $training) }}" onsubmit="return confirm('Are you sure you want to delete this training?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50">
                                    Delete Training
                                </button>
                            </form>
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

        function inviteeRepeater() {
            const existingInvitees = @json($training->invitees->pluck('email')->toArray());
            const emails = existingInvitees.length > 0 ? existingInvitees : [''];
            return {
                invitees: emails.map(email => ({ email: email, status: '', message: '', name: '', checking: false })),
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
