<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Attendees: {{ $training->title }}
            </h2>
            <a href="{{ route('trainer.attendees.export', $training) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </a>
        </div>
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

            {{-- Training Summary --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 flex flex-wrap items-center gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Date:</span>
                        <span class="font-medium text-gray-900">{{ $training->start_date->format('M j, Y \a\t g:i A') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Registered:</span>
                        <span class="font-medium text-gray-900">{{ $attendees->count() }}{{ $training->max_attendees ? ' / ' . $training->max_attendees : '' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Type:</span>
                        @php
                            $typeValue = is_object($training->type) ? $training->type->value : $training->type;
                        @endphp
                        <span class="font-medium text-gray-900">{{ ucfirst(str_replace('_', ' ', $typeValue)) }}</span>
                    </div>
                    <a href="{{ route('trainer.trainings.edit', $training) }}" class="ml-auto text-sm font-medium text-brand-primary">Edit Training</a>
                </div>
            </div>

            {{-- Attendee Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($attendees->count() > 0)
                    <form method="POST" action="{{ route('trainer.attendees.bulk-complete', $training) }}">
                        @csrf

                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left">
                                            <input type="checkbox" id="select-all" class="rounded border-gray-300 text-brand-primary">
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($attendees as $reg)
                                        @php
                                            $regStatusValue = is_object($reg->status) ? $reg->status->value : $reg->status;
                                            $regStatusColors = [
                                                'registered' => 'bg-blue-100 text-blue-800',
                                                'attended' => 'bg-yellow-100 text-yellow-800',
                                                'completed' => 'bg-green-100 text-green-800',
                                                'no_show' => 'bg-red-100 text-red-800',
                                                'canceled' => 'bg-gray-100 text-gray-800',
                                            ];
                                            $regStatusColor = $regStatusColors[$regStatusValue] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4">
                                                @if ($regStatusValue !== 'completed')
                                                    <input type="checkbox" name="registration_ids[]" value="{{ $reg->id }}" class="rounded border-gray-300 attendee-check text-brand-primary">
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $reg->user->full_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $reg->user->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $reg->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $regStatusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $regStatusValue)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                @if ($reg->amount_paid_cents > 0)
                                                    ${{ number_format($reg->amount_paid_cents / 100, 2) }}
                                                @else
                                                    <span class="text-green-600">Free</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                                @if ($regStatusValue !== 'completed')
                                                    <button type="button" onclick="event.preventDefault(); document.getElementById('complete-{{ $reg->id }}').submit();" class="font-medium text-green-600 hover:text-green-800">
                                                        Mark Complete
                                                    </button>
                                                @else
                                                    <span class="text-gray-400">Completed {{ $reg->completed_at ? $reg->completed_at->format('M j') : '' }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden divide-y divide-gray-200">
                            @foreach ($attendees as $reg)
                                @php
                                    $regStatusValue = is_object($reg->status) ? $reg->status->value : $reg->status;
                                    $regStatusColor = $regStatusColors[$regStatusValue] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <div class="p-4">
                                    <div class="flex items-center justify-between mb-1">
                                        <div class="flex items-center space-x-2">
                                            @if ($regStatusValue !== 'completed')
                                                <input type="checkbox" name="registration_ids[]" value="{{ $reg->id }}" class="rounded border-gray-300 attendee-check text-brand-primary">
                                            @endif
                                            <span class="text-sm font-medium text-gray-900">{{ $reg->user->full_name }}</span>
                                        </div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $regStatusColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $regStatusValue)) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 ml-6">{{ $reg->user->email }}</p>
                                    @if ($regStatusValue !== 'completed')
                                        <button type="button" onclick="event.preventDefault(); document.getElementById('complete-{{ $reg->id }}').submit();" class="mt-2 ml-6 text-xs font-medium text-green-600">
                                            Mark Complete
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Bulk Action Bar --}}
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Mark Selected as Complete
                            </button>
                            <span class="text-sm text-gray-500">{{ $attendees->count() }} total attendees</span>
                        </div>
                    </form>
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Attendees</h3>
                        <p class="mt-1 text-sm text-gray-500">No one has registered for this training yet.</p>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <a href="{{ route('trainer.trainings.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Trainings
                </a>
            </div>
        </div>
    </div>

    {{-- Individual Complete Forms --}}
    @foreach ($attendees as $reg)
        @php
            $regStatusValue = is_object($reg->status) ? $reg->status->value : $reg->status;
        @endphp
        @if ($regStatusValue !== 'completed')
            <form id="complete-{{ $reg->id }}" method="POST" action="{{ route('trainer.attendees.complete', [$training, $reg]) }}" class="hidden">
                @csrf
            </form>
        @endif
    @endforeach

    <script>
        document.getElementById('select-all')?.addEventListener('change', function() {
            document.querySelectorAll('.attendee-check').forEach(function(cb) {
                cb.checked = this.checked;
            }.bind(this));
        });
    </script>
</x-app-layout>
