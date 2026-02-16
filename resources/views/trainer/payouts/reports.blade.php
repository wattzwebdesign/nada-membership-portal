<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Earnings Reports') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Date Filter --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4" style="color: #374269;">Filter by Date Range</h3>
                    <form method="GET" action="{{ route('trainer.payouts.reports') }}" class="flex flex-col sm:flex-row items-end gap-4">
                        <div class="flex-1 w-full sm:w-auto">
                            <label for="from" class="block text-sm font-medium text-gray-700">From</label>
                            <input type="text" name="from" id="from" value="{{ request('from') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" data-datepicker='{"altInput":true,"altFormat":"M j, Y","dateFormat":"Y-m-d"}'>
                        </div>
                        <div class="flex-1 w-full sm:w-auto">
                            <label for="to" class="block text-sm font-medium text-gray-700">To</label>
                            <input type="text" name="to" id="to" value="{{ request('to') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" data-datepicker='{"altInput":true,"altFormat":"M j, Y","dateFormat":"Y-m-d"}'>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white" style="background-color: #374269;">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                                Filter
                            </button>
                            @if (request('from') || request('to'))
                                <a href="{{ route('trainer.payouts.reports') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                                    Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Your Earnings</p>
                        <p class="mt-2 text-3xl font-bold" style="color: #d39c27;">${{ number_format(($report['trainer_earnings'] ?? 0) / 100, 2) }}</p>
                        @if (request('from') || request('to'))
                            <p class="mt-1 text-xs text-gray-400">
                                {{ request('from') ? request('from') : 'All time' }} - {{ request('to') ? request('to') : 'Present' }}
                            </p>
                        @else
                            <p class="mt-1 text-xs text-gray-400">All time</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                        <p class="mt-2 text-3xl font-bold" style="color: #374269;">${{ number_format(($report['total_revenue'] ?? 0) / 100, 2) }}</p>
                        <p class="mt-1 text-xs text-gray-400">Gross revenue from paid trainings</p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Platform Fees</p>
                        <p class="mt-2 text-3xl font-bold text-gray-500">${{ number_format(($report['platform_fees'] ?? 0) / 100, 2) }}</p>
                        <p class="mt-1 text-xs text-gray-400">NADA platform processing fees</p>
                    </div>
                </div>
            </div>

            {{-- Per-Training Breakdown --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4" style="color: #374269;">Per-Training Breakdown</h3>

                    @if (isset($report['per_training']) && count($report['per_training']) > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Attendees</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform Fee</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Your Payout</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($report['per_training'] as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item['training_title'] }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item['date'] ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item['paid_attendees'] }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($item['total_revenue'] / 100, 2) }}</td>
                                            <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($item['platform_fee'] / 100, 2) }}</td>
                                            <td class="px-6 py-4 text-sm font-semibold" style="color: #d39c27;">${{ number_format($item['trainer_payout'] / 100, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="3" class="px-6 py-3 text-sm font-semibold text-gray-900">Total</td>
                                        <td class="px-6 py-3 text-sm font-semibold text-gray-900">${{ number_format(($report['total_revenue'] ?? 0) / 100, 2) }}</td>
                                        <td class="px-6 py-3 text-sm font-semibold text-gray-500">${{ number_format(($report['platform_fees'] ?? 0) / 100, 2) }}</td>
                                        <td class="px-6 py-3 text-sm font-bold" style="color: #d39c27;">${{ number_format(($report['trainer_earnings'] ?? 0) / 100, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach ($report['per_training'] as $item)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $item['training_title'] }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $item['date'] ?? '' }} | {{ $item['paid_attendees'] }} paid attendees</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-xs text-gray-500">Revenue: ${{ number_format($item['total_revenue'] / 100, 2) }}</span>
                                        <span class="text-sm font-semibold" style="color: #d39c27;">Payout: ${{ number_format($item['trainer_payout'] / 100, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <p class="mt-2 text-sm text-gray-500">No earnings data for this period.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('trainer.payouts.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to Payouts
                </a>
                <a href="{{ route('trainer.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">Trainer Dashboard</a>
            </div>
        </div>
    </div>
</x-app-layout>
