<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Clinical Submissions') }}
        </h2>
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
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Submission History</h3>
                        <a href="{{ route('clinicals.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New Submission
                        </a>
                    </div>

                    @if ($clinicals->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trainer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($clinicals as $clinical)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $clinical->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $clinical->estimated_training_date ? $clinical->estimated_training_date->format('M j, Y') : 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $clinical->trainer->full_name ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $clinicalStatusColors = [
                                                        'submitted' => 'bg-blue-100 text-blue-800',
                                                        'under_review' => 'bg-yellow-100 text-yellow-800',
                                                        'approved' => 'bg-green-100 text-green-800',
                                                        'rejected' => 'bg-red-100 text-red-800',
                                                    ];
                                                    $clinicalStatusColor = $clinicalStatusColors[$clinical->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $clinicalStatusColor }}">
                                                    {{ ucfirst(str_replace('_', ' ', $clinical->status)) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                                {{ $clinical->notes ?? '--' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($clinicals as $clinical)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $clinical->created_at->format('M j, Y') }}</span>
                                        @php
                                            $clinicalStatusColor = $clinicalStatusColors[$clinical->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $clinicalStatusColor }}">
                                            {{ ucfirst(str_replace('_', ' ', $clinical->status)) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">Trainer: {{ $clinical->trainer->full_name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">Training Date: {{ $clinical->estimated_training_date ? $clinical->estimated_training_date->format('M j, Y') : 'N/A' }}</p>
                                    @if ($clinical->notes)
                                        <p class="text-xs text-gray-400 mt-1 truncate">{{ $clinical->notes }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($clinicals->hasPages())
                            <div class="mt-6">
                                {{ $clinicals->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Submissions</h3>
                            <p class="mt-1 text-sm text-gray-500">You haven't submitted any clinical documents yet.</p>
                            <div class="mt-6">
                                <a href="{{ route('clinicals.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                                    Submit Clinicals
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
