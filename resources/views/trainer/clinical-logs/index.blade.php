<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Clinical Log Books</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary mb-6">Assigned Log Books</h3>

                    @if ($logs->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($logs as $log)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $log->user->full_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $log->user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @php
                                                    $totalHours = (float) ($log->entries_sum_hours ?? 0);
                                                    $threshold = (float) \App\Models\SiteSetting::get('clinical_hours_threshold', '40');
                                                @endphp
                                                {{ number_format($totalHours, 1) }} / {{ number_format($threshold, 0) }} hrs
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'in_progress' => 'bg-blue-100 text-blue-800',
                                                        'completed' => 'bg-yellow-100 text-yellow-800',
                                                        'approved' => 'bg-green-100 text-green-800',
                                                        'rejected' => 'bg-red-100 text-red-800',
                                                    ];
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$log->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $log->status->label() }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $log->created_at->format('M j, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <a href="{{ route('trainer.clinical-logs.show', $log) }}" class="text-sm text-brand-primary hover:underline">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($logs as $log)
                                <a href="{{ route('trainer.clinical-logs.show', $log) }}" class="block border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $log->user->full_name }}</span>
                                        @php
                                            $statusColors = [
                                                'in_progress' => 'bg-blue-100 text-blue-800',
                                                'completed' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$log->status->value] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $log->status->label() }}
                                        </span>
                                    </div>
                                    @php
                                        $totalHours = (float) ($log->entries_sum_hours ?? 0);
                                        $threshold = (float) \App\Models\SiteSetting::get('clinical_hours_threshold', '40');
                                    @endphp
                                    <p class="text-xs text-gray-500">{{ number_format($totalHours, 1) }} / {{ number_format($threshold, 0) }} hrs</p>
                                    <p class="text-xs text-gray-400">{{ $log->created_at->format('M j, Y') }}</p>
                                </a>
                            @endforeach
                        </div>

                        @if ($logs->hasPages())
                            <div class="mt-6">
                                {{ $logs->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Log Books Assigned</h3>
                            <p class="mt-1 text-sm text-gray-500">Clinical log books assigned to you will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
