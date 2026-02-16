<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold" style="color: #374269;">Earnings Dashboard</h2>
            </div>

            {{-- Date Filter --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <form wire:submit="loadReport" class="flex flex-col sm:flex-row items-end gap-4">
                        <div class="flex-1">
                            <label for="dateFrom" class="block text-sm font-medium text-gray-700 mb-1">From</label>
                            <input type="text" id="dateFrom" wire:model="dateFrom"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 text-sm"
                                   x-data x-init="flatpickr($el, {altInput:true,altFormat:'M j, Y',dateFormat:'Y-m-d',onChange:(d,s)=>{$wire.set('dateFrom',s)}})">
                            @error('dateFrom')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex-1">
                            <label for="dateTo" class="block text-sm font-medium text-gray-700 mb-1">To</label>
                            <input type="text" id="dateTo" wire:model="dateTo"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 text-sm"
                                   x-data x-init="flatpickr($el, {altInput:true,altFormat:'M j, Y',dateFormat:'Y-m-d',onChange:(d,s)=>{$wire.set('dateTo',s)}})">
                            @error('dateTo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 rounded-md text-white text-sm font-medium shadow-sm hover:opacity-90 transition-opacity"
                                style="background-color: #374269;">
                            <svg wire:loading wire:target="loadReport" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Load Report
                        </button>
                    </form>
                </div>
            </div>

            {{-- Summary Cards --}}
            @if (!empty($earnings))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Total Revenue --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Revenue</dt>
                            <dd class="mt-2 text-3xl font-bold" style="color: #374269;">
                                ${{ number_format(($earnings['total_revenue'] ?? 0) / 100, 2) }}
                            </dd>
                        </div>
                    </div>

                    {{-- Platform Fees --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Platform Fees</dt>
                            <dd class="mt-2 text-3xl font-bold text-gray-600">
                                ${{ number_format(($earnings['platform_fees'] ?? 0) / 100, 2) }}
                            </dd>
                        </div>
                    </div>

                    {{-- Your Earnings --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <dt class="text-sm font-medium text-gray-500 truncate">Your Earnings</dt>
                            <dd class="mt-2 text-3xl font-bold" style="color: #d39c27;">
                                ${{ number_format(($earnings['trainer_earnings'] ?? 0) / 100, 2) }}
                            </dd>
                        </div>
                    </div>
                </div>

                {{-- Per-Training Breakdown --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4" style="color: #374269;">Per-Training Breakdown</h3>

                        @php
                            $perTraining = $earnings['per_training'] ?? collect();
                        @endphp

                        @if (count($perTraining) === 0)
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No earnings data for this period.</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Training</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paid Attendees</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform Fee</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Your Payout</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($perTraining as $item)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $item['training_title'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $item['paid_attendees'] }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    ${{ number_format($item['total_revenue'] / 100, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    ${{ number_format($item['platform_fee'] / 100, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" style="color: #d39c27;">
                                                    ${{ number_format($item['trainer_payout'] / 100, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td class="px-6 py-3 text-sm font-bold text-gray-900">Total</td>
                                            <td class="px-6 py-3 text-sm text-gray-500"></td>
                                            <td class="px-6 py-3 text-sm font-bold text-gray-900">
                                                ${{ number_format(($earnings['total_revenue'] ?? 0) / 100, 2) }}
                                            </td>
                                            <td class="px-6 py-3 text-sm font-bold text-gray-500">
                                                ${{ number_format(($earnings['platform_fees'] ?? 0) / 100, 2) }}
                                            </td>
                                            <td class="px-6 py-3 text-sm font-bold" style="color: #d39c27;">
                                                ${{ number_format(($earnings['trainer_earnings'] ?? 0) / 100, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
