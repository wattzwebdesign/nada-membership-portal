<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $training->title }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            @php
                $statusValue = is_object($training->status) ? $training->status->value : $training->status;
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

                $typeBadgeColors = [
                    'in_person' => 'bg-blue-100 text-blue-800',
                    'virtual' => 'bg-purple-100 text-purple-800',
                    'hybrid' => 'bg-indigo-100 text-indigo-800',
                ];
                $typeValue = is_object($training->type) ? $training->type->value : $training->type;
                $typeBadgeColor = $typeBadgeColors[$typeValue] ?? 'bg-gray-100 text-gray-800';
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Status Alerts --}}
                    @if ($statusValue === 'denied' && $training->denied_reason)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-red-800">Training Denied</h4>
                                    <p class="text-sm text-red-700 mt-1">{{ $training->denied_reason }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($statusValue === 'pending_approval')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <svg class="h-5 w-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <h4 class="text-sm font-semibold text-yellow-800">Pending Approval</h4>
                                    <p class="text-sm text-yellow-700 mt-1">This training is awaiting admin review. You will be notified once it has been approved or denied.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            {{-- Badges --}}
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeColor }}">
                                    {{ $statusLabel }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $typeValue)) }}
                                </span>
                                @if ($training->is_group)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">Group</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Private</span>
                                @endif
                                @if ($training->is_paid)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: #fef3c7; color: #92400e;">
                                        Paid &mdash; ${{ number_format($training->price_cents / 100, 2) }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Free</span>
                                @endif
                            </div>

                            <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $training->title }}</h3>

                            @if ($training->description)
                                <div class="mb-6">
                                    <div class="prose max-w-none text-gray-600">
                                        {!! nl2br(e($training->description)) !!}
                                    </div>
                                </div>
                            @endif

                            {{-- Details Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-200 pt-6">
                                {{-- Date & Time --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Date & Time</p>
                                        <p class="text-sm text-gray-500">{{ $training->start_date->format('l, F j, Y') }}</p>
                                        <p class="text-sm text-gray-500">{{ $training->start_date->format('g:i A') }} - {{ $training->end_date->format('g:i A') }} {{ $training->timezone }}</p>
                                    </div>
                                </div>

                                {{-- Location --}}
                                @if ($training->type !== App\Enums\TrainingType::Virtual)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Location</p>
                                            @if ($training->location_name)
                                                <p class="text-sm text-gray-500">{{ $training->location_name }}</p>
                                            @endif
                                            @if ($training->location_address)
                                                <p class="text-sm text-gray-500">{{ $training->location_address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Virtual Link --}}
                                @if (in_array($training->type, [App\Enums\TrainingType::Virtual, App\Enums\TrainingType::Hybrid]) && $training->virtual_link)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Virtual Meeting</p>
                                            <a href="{{ $training->virtual_link }}" target="_blank" class="text-sm hover:underline" style="color: #374269;">{{ $training->virtual_link }}</a>
                                        </div>
                                    </div>
                                @endif

                                {{-- Capacity --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" style="color: #374269;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Attendees</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $training->registrations_count ?? 0 }} registered{{ $training->max_attendees ? ' / ' . $training->max_attendees . ' max' : '' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Invitees Section (Group Trainings) --}}
                    @if ($training->is_group && $training->invitees->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold mb-4" style="color: #374269;">Invitees ({{ $training->invitees->count() }})</h4>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notified</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach ($training->invitees as $invitee)
                                                <tr>
                                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $invitee->email }}</td>
                                                    <td class="px-4 py-3 text-sm">
                                                        @if ($invitee->notified_at)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Sent {{ $invitee->notified_at->format('M j, Y') }}
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                                Not yet
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar: Actions --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6 space-y-3">
                            <h4 class="text-lg font-semibold mb-4" style="color: #374269;">Actions</h4>

                            {{-- Edit Training --}}
                            @if (in_array($statusValue, ['pending_approval', 'denied']))
                                <a href="{{ route('trainer.trainings.edit', $training) }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white transition" style="background-color: #374269;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ $statusValue === 'denied' ? 'Edit & Resubmit' : 'Edit Training' }}
                                </a>
                            @endif

                            {{-- View Attendees --}}
                            @if (in_array($statusValue, ['published', 'completed']))
                                <a href="{{ route('trainer.attendees.index', $training) }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    View Attendees ({{ $training->registrations_count ?? 0 }})
                                </a>

                                <a href="{{ route('trainer.broadcasts.index', ['training_id' => $training->id]) }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    Broadcast Email
                                </a>
                            @endif

                            {{-- Cancel Training --}}
                            @if ($statusValue === 'published')
                                <form method="POST" action="{{ route('trainer.trainings.cancel', $training) }}" onsubmit="return confirm('Are you sure you want to cancel this training? This action cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Cancel Training
                                    </button>
                                </form>
                            @endif

                            {{-- Mark Complete --}}
                            @if ($statusValue === 'published')
                                <form method="POST" action="{{ route('trainer.trainings.complete', $training) }}" onsubmit="return confirm('Mark this training as completed?');">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-green-300 text-sm font-medium rounded-md text-green-700 hover:bg-green-50 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Mark Complete
                                    </button>
                                </form>
                            @endif

                            {{-- Delete Training --}}
                            @if (in_array($statusValue, ['pending_approval', 'denied']))
                                <form method="POST" action="{{ route('trainer.trainings.destroy', $training) }}" onsubmit="return confirm('Are you sure you want to delete this training? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Delete Training
                                    </button>
                                </form>
                            @endif

                            <hr class="my-2">

                            {{-- Back to Trainings --}}
                            <a href="{{ route('trainer.trainings.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Back to Trainings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
