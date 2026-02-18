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

            @if (session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-brand-primary">Assigned Clinicals</h3>
                        <span class="text-sm text-gray-500">{{ $clinicals->total() }} total</span>
                    </div>

                    @if ($clinicals->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($clinicals as $clinical)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $clinical->first_name }} {{ $clinical->last_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $clinical->email }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $clinical->estimated_training_date?->format('M j, Y') ?? 'N/A' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $clinical->created_at->format('M j, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if ($clinical->status === 'submitted')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Submitted</span>
                                                @elseif ($clinical->status === 'under_review')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Under Review</span>
                                                @elseif ($clinical->status === 'approved')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                                @elseif ($clinical->status === 'rejected')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <a href="{{ route('trainer.clinicals.show', $clinical) }}" class="inline-flex items-center px-3 py-1.5 border text-xs font-medium rounded-md hover:bg-gray-50 transition border-brand-primary text-brand-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($clinicals as $clinical)
                                <a href="{{ route('trainer.clinicals.show', $clinical) }}" class="block border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $clinical->first_name }} {{ $clinical->last_name }}</span>
                                        @if ($clinical->status === 'submitted')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Submitted</span>
                                        @elseif ($clinical->status === 'under_review')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Under Review</span>
                                        @elseif ($clinical->status === 'approved')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Approved</span>
                                        @elseif ($clinical->status === 'rejected')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rejected</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $clinical->email }}</p>
                                    <p class="text-xs text-gray-500">Submitted: {{ $clinical->created_at->format('M j, Y') }}</p>
                                </a>
                            @endforeach
                        </div>

                        @if ($clinicals->hasPages())
                            <div class="mt-6">
                                {{ $clinicals->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 011.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C8.003 4.025 7 5.089 7 6.358V18a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0019 18V6.358c0-1.269-1.003-2.333-2.226-2.442-.374-.03-.748-.057-1.124-.08"/></svg>
                            <h3 class="mt-3 text-sm font-medium text-gray-900">No Clinical Submissions</h3>
                            <p class="mt-1 text-sm text-gray-500">Clinical submissions assigned to you will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
