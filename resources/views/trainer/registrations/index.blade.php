<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                All Registrations
                <span class="text-sm font-normal text-gray-500">({{ $registrations->total() }} total)</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filter Bar --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <form method="GET" action="{{ route('trainer.registrations.index') }}" class="p-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        {{-- Search --}}
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>

                        {{-- Training Filter --}}
                        <div class="sm:w-64">
                            <select name="training" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Trainings</option>
                                @foreach ($trainings as $training)
                                    <option value="{{ $training->id }}" {{ request('training') == $training->id ? 'selected' : '' }}>
                                        {{ $training->title }} — {{ $training->start_date->format('M j, Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Status Filter --}}
                        <div class="sm:w-40">
                            <select name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">All Statuses</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                                        {{ $status->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Buttons --}}
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                Filter
                            </button>
                            @if (request('search') || request('training') || request('status'))
                                <a href="{{ route('trainer.registrations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- Registrations Table --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($registrations->count() > 0)

                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($registrations as $reg)
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $reg->user->full_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $reg->user->email }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <a href="{{ route('trainer.attendees.index', $reg->training) }}" class="font-medium hover:underline" style="color: #374269;">
                                                {{ $reg->training->title }}
                                            </a>
                                            <p class="text-xs text-gray-400">{{ $reg->training->start_date->format('M j, Y') }}</p>
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
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($registrations as $reg)
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
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $reg->user->full_name }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $regStatusColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $regStatusValue)) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">{{ $reg->user->email }}</p>
                                <p class="text-xs text-gray-400 mt-1">
                                    <a href="{{ route('trainer.attendees.index', $reg->training) }}" class="hover:underline" style="color: #374269;">{{ $reg->training->title }}</a>
                                    — {{ $reg->training->start_date->format('M j, Y') }}
                                </p>
                                <div class="flex items-center justify-between mt-1.5">
                                    <span class="text-xs text-gray-500">Registered {{ $reg->created_at->format('M j, Y') }}</span>
                                    <span class="text-xs text-gray-500">
                                        @if ($reg->amount_paid_cents > 0)
                                            ${{ number_format($reg->amount_paid_cents / 100, 2) }}
                                        @else
                                            <span class="text-green-600">Free</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Registrations Found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if (request('search') || request('training') || request('status'))
                                No registrations match your filters. Try adjusting your search.
                            @else
                                No one has registered for any of your trainings yet.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            {{-- Pagination --}}
            @if ($registrations->hasPages())
                <div class="mt-4">
                    {{ $registrations->links() }}
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('trainer.dashboard') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
