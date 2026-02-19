<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Orders') }}
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

            @if (session('warning'))
                <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-md">
                    {{ session('warning') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if (isset($orders) && $orders->count() > 0)
                    {{-- Desktop Table --}}
                    <div class="hidden md:block overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Your Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shipped</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivered</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($orders as $split)
                                    @php
                                        $splitStatus = is_object($split->status) ? $split->status->value : $split->status;
                                        $splitStatusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'shipped' => 'bg-indigo-100 text-indigo-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                            'canceled' => 'bg-red-100 text-red-800',
                                            'refunded' => 'bg-gray-100 text-gray-800',
                                        ];
                                        $splitStatusColor = $splitStatusColors[$splitStatus] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a href="{{ route('vendor.orders.show', $split->order) }}" class="text-sm font-medium hover:underline text-brand-primary">#{{ $split->order->order_number }}</a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $split->order->user->full_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $split->items_count ?? $split->items->count() }} item{{ ($split->items_count ?? $split->items->count()) !== 1 ? 's' : '' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            ${{ number_format($split->vendor_amount / 100, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $splitStatusColor }}">
                                                {{ ucfirst($splitStatus) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $split->shipped_at ? $split->shipped_at->format('M j, Y') : '--' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $split->delivered_at ? $split->delivered_at->format('M j, Y') : '--' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('vendor.orders.show', $split->order) }}" class="font-medium text-brand-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Cards --}}
                    <div class="md:hidden divide-y divide-gray-200">
                        @foreach ($orders as $split)
                            @php
                                $splitStatus = is_object($split->status) ? $split->status->value : $split->status;
                                $splitStatusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'shipped' => 'bg-indigo-100 text-indigo-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'canceled' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                ];
                                $splitStatusColor = $splitStatusColors[$splitStatus] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <div class="p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <a href="{{ route('vendor.orders.show', $split->order) }}" class="text-sm font-medium hover:underline text-brand-primary">#{{ $split->order->order_number }}</a>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $splitStatusColor }} flex-shrink-0 ml-2">
                                        {{ ucfirst($splitStatus) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">{{ $split->order->user->full_name ?? 'N/A' }} | {{ $split->items_count ?? $split->items->count() }} item{{ ($split->items_count ?? $split->items->count()) !== 1 ? 's' : '' }}</p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="text-sm font-semibold text-gray-900">${{ number_format($split->vendor_amount / 100, 2) }}</span>
                                    <a href="{{ route('vendor.orders.show', $split->order) }}" class="text-xs font-medium text-brand-primary">View Details</a>
                                </div>
                                @if ($split->shipped_at)
                                    <p class="text-xs text-gray-400 mt-1">Shipped {{ $split->shipped_at->format('M j, Y') }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if ($orders->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200">
                            {{ $orders->links() }}
                        </div>
                    @endif
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <h3 class="mt-3 text-sm font-medium text-gray-900">No Orders Yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Orders will appear here once customers purchase your products.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
