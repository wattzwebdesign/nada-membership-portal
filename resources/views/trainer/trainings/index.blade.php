<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Trainings') }}
            </h2>
            <a href="{{ route('trainer.trainings.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Training
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if (isset($trainings) && $trainings->count() > 0)
                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendees</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($trainings as $training)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <p class="text-sm font-medium text-gray-900">{{ $training->title }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $training->start_date->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $typeBadgeColors = [
                                                    'in_person' => 'bg-blue-100 text-blue-800',
                                                    'virtual' => 'bg-purple-100 text-purple-800',
                                                    'hybrid' => 'bg-indigo-100 text-indigo-800',
                                                ];
                                                $typeValue = is_object($training->type) ? $training->type->value : $training->type;
                                                $typeBadgeColor = $typeBadgeColors[$typeValue] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadgeColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $typeValue)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
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
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadgeColor }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $training->registrations_count ?? 0 }}{{ $training->max_attendees ? ' / ' . $training->max_attendees : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if ($training->is_paid)
                                                ${{ number_format($training->price_cents / 100, 2) }}
                                            @else
                                                <span class="text-green-600">Free</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2">
                                            <a href="{{ route('trainer.trainings.edit', $training) }}" class="font-medium" style="color: #374269;">Edit</a>
                                            <a href="{{ route('trainer.attendees.index', $training) }}" class="font-medium text-gray-600 hover:text-gray-900">Attendees</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($trainings as $training)
                            @php
                                $statusValue = is_object($training->status) ? $training->status->value : $training->status;
                                $mobileStatusBadgeColors = [
                                    'pending_approval' => 'bg-yellow-100 text-yellow-800',
                                    'published' => 'bg-green-100 text-green-800',
                                    'denied' => 'bg-red-100 text-red-800',
                                    'canceled' => 'bg-red-100 text-red-800',
                                    'completed' => 'bg-blue-100 text-blue-800',
                                ];
                                $mobileStatusBadgeColor = $mobileStatusBadgeColors[$statusValue] ?? 'bg-gray-100 text-gray-800';
                                $mobileStatusLabels = [
                                    'pending_approval' => 'Pending Approval',
                                    'published' => 'Published',
                                    'denied' => 'Denied',
                                    'canceled' => 'Canceled',
                                    'completed' => 'Completed',
                                ];
                                $mobileStatusLabel = $mobileStatusLabels[$statusValue] ?? ucfirst($statusValue);
                                $typeValue = is_object($training->type) ? $training->type->value : $training->type;
                            @endphp
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-medium text-gray-900">{{ $training->title }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $mobileStatusBadgeColor }}">
                                        {{ $mobileStatusLabel }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">{{ $training->start_date->format('M j, Y \a\t g:i A') }}</p>
                                <p class="text-xs text-gray-400">{{ $training->registrations_count ?? 0 }} attendees | {{ ucfirst(str_replace('_', ' ', $typeValue)) }}</p>
                                <div class="mt-2 flex space-x-3">
                                    <a href="{{ route('trainer.trainings.edit', $training) }}" class="text-xs font-medium" style="color: #374269;">Edit</a>
                                    <a href="{{ route('trainer.attendees.index', $training) }}" class="text-xs font-medium text-gray-600">Attendees</a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($trainings->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $trainings->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Trainings Yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Create your first training to get started.</p>
                        <div class="mt-6">
                            <a href="{{ route('trainer.trainings.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                Create Training
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
