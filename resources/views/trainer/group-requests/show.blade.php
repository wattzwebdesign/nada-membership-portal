<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Group Training Request') }}
            </h2>
            <a href="{{ route('trainer.group-requests.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                &larr; Back to Requests
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Status Banner --}}
            @if ($groupRequest->training)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Training Created</p>
                            <p class="text-xs text-green-600">Linked to: {{ $groupRequest->training->title }}</p>
                        </div>
                    </div>
                    <a href="{{ route('trainer.trainings.show', $groupRequest->training) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md border border-green-300 text-green-700 hover:bg-green-100 transition">
                        View Training
                    </a>
                </div>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-medium text-amber-800">Needs Training</p>
                            <p class="text-xs text-amber-600">Create a training from this request to get started.</p>
                        </div>
                    </div>
                    <a href="{{ route('trainer.trainings.create', ['from_request' => $groupRequest->id]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-brand-primary hover:bg-brand-accent transition">
                        Create Training
                    </a>
                </div>
            @endif

            {{-- Request Details --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary mb-4">Request Details</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Training Name</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $groupRequest->training_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Training Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $groupRequest->training_date->format('M j, Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Location</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $groupRequest->training_city }}, {{ $groupRequest->training_state }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Number of Tickets</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $groupRequest->number_of_tickets }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Company Contact --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary mb-4">Company Contact</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Contact Name</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $groupRequest->company_full_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</p>
                            <p class="mt-1 text-sm text-gray-900">
                                <a href="mailto:{{ $groupRequest->company_email }}" class="text-brand-primary hover:underline">{{ $groupRequest->company_email }}</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cost Breakdown --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary mb-4">Payment Details</h3>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Cost per Ticket</span>
                            <span class="text-gray-900">${{ number_format($groupRequest->cost_per_ticket_cents / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tickets</span>
                            <span class="text-gray-900">&times; {{ $groupRequest->number_of_tickets }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-100 pt-2">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-900">${{ number_format($groupRequest->subtotal_cents / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Transaction Fee</span>
                            <span class="text-gray-900">${{ number_format($groupRequest->transaction_fee_cents / 100, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 font-semibold">
                            <span class="text-gray-700">Total Paid</span>
                            <span class="text-gray-900">{{ $groupRequest->total_formatted }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-400">Paid</span>
                            <span class="text-gray-400">{{ $groupRequest->paid_at->format('M j, Y \a\t g:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Team Members --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-brand-primary mb-4">Team Members ({{ $groupRequest->members->count() }})</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($groupRequest->members as $member)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->first_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $member->last_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $member->email }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
