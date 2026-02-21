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

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-brand-primary">Your Log Books</h3>
                    <p class="text-sm text-gray-500 mt-1">Track your clinical hours toward your {{ $threshold }}-hour requirement.</p>
                </div>
                <a href="{{ route('clinical-logs.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:opacity-90 transition">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Log Book
                </a>
            </div>

            @if ($logs->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($logs as $log)
                        <a href="{{ route('clinical-logs.show', $log) }}" class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow">
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm text-gray-500">Log #{{ $log->id }}</span>
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
                                </div>

                                <p class="text-sm font-medium text-gray-900 mb-1">
                                    Trainer: {{ $log->trainer?->full_name ?? 'Not assigned' }}
                                </p>
                                <p class="text-xs text-gray-500 mb-3">
                                    {{ $log->entries_count }} {{ Str::plural('entry', $log->entries_count) }}
                                </p>

                                {{-- Progress bar --}}
                                @php
                                    $totalHours = (float) ($log->entries_sum_hours ?? 0);
                                    $percent = $threshold > 0 ? min(100, round(($totalHours / $threshold) * 100, 1)) : 100;
                                @endphp
                                <div class="mb-1">
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>{{ number_format($totalHours, 1) }} / {{ number_format($threshold, 0) }} hrs</span>
                                        <span>{{ $percent }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full transition-all {{ $percent >= 100 ? 'bg-green-500' : 'bg-brand-secondary' }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>

                                <p class="text-xs text-gray-400 mt-3">Created {{ $log->created_at->format('M j, Y') }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Log Books</h3>
                        <p class="mt-1 text-sm text-gray-500">Create a log book to start tracking your clinical hours.</p>
                        <div class="mt-6">
                            <a href="{{ route('clinical-logs.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:opacity-90 transition">
                                Create Log Book
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if ($hasLegacyClinicals)
                <div class="mt-8">
                    <a href="{{ route('clinicals.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 transition">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        View Legacy Clinical Submissions
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
