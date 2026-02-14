<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold" style="color: #374269;">Attendees</h2>
                            <p class="mt-1 text-sm text-gray-600">{{ $training->title }} &mdash; {{ $training->start_date->format('M d, Y') }}</p>
                        </div>
                        @if (count($selectedIds) > 0)
                            <button wire:click="bulkComplete"
                                    wire:confirm="Mark {{ count($selectedIds) }} selected attendee(s) as completed?"
                                    class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity"
                                    style="background-color: #374269;">
                                Mark Selected Complete ({{ count($selectedIds) }})
                            </button>
                        @endif
                    </div>

                    @if (session('success'))
                        <div class="mb-4 rounded-md bg-green-50 p-4">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    @endif

                    @if ($attendees->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-semibold text-gray-900">No attendees yet</h3>
                            <p class="mt-1 text-sm text-gray-500">No one has registered for this training yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left">
                                            <input type="checkbox"
                                                   wire:model.live="selectedIds"
                                                   class="rounded border-gray-300"
                                                   @if ($attendees->every(fn ($a) => $a->status === \App\Enums\RegistrationStatus::Completed)) disabled @endif>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($attendees as $attendee)
                                        <tr>
                                            <td class="px-6 py-4">
                                                @if ($attendee->status !== \App\Enums\RegistrationStatus::Completed)
                                                    <input type="checkbox"
                                                           wire:model.live="selectedIds"
                                                           value="{{ $attendee->id }}"
                                                           class="rounded border-gray-300">
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $attendee->user->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $attendee->user->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $attendee->created_at->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'registered' => 'bg-blue-100 text-blue-800',
                                                        'attended' => 'bg-yellow-100 text-yellow-800',
                                                        'completed' => 'bg-green-100 text-green-800',
                                                        'no_show' => 'bg-red-100 text-red-800',
                                                        'canceled' => 'bg-gray-100 text-gray-800',
                                                    ];
                                                    $colorClass = $statusColors[$attendee->status->value] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $colorClass }}">
                                                    {{ $attendee->status->label() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                @if ($attendee->status !== \App\Enums\RegistrationStatus::Completed)
                                                    <button wire:click="markComplete({{ $attendee->id }})"
                                                            wire:confirm="Mark {{ $attendee->user->full_name }} as completed?"
                                                            class="text-sm font-medium hover:underline"
                                                            style="color: #d39c27;">
                                                        Mark Complete
                                                    </button>
                                                @else
                                                    <span class="text-sm text-green-600 flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                        </svg>
                                                        Completed
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-sm text-gray-500">
                            Total attendees: {{ $attendees->count() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
