<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order') }} #{{ $order->order_number }}
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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Order Status --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-brand-primary">Order Details</h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $splitStatusColor }}">
                                    {{ ucfirst($splitStatus) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="font-medium text-gray-900">Order Number</p>
                                    <p class="text-gray-500">#{{ $order->order_number }}</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Order Date</p>
                                    <p class="text-gray-500">{{ $order->created_at->format('M j, Y \a\t g:i A') }}</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Customer</p>
                                    <p class="text-gray-500">{{ $order->customer_full_name }}</p>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Customer Email</p>
                                    <p class="text-gray-500">{{ $order->customer_email }}</p>
                                </div>
                                @if ($order->shipping_address_line_1)
                                    <div class="sm:col-span-2">
                                        <p class="font-medium text-gray-900">Shipping Address</p>
                                        <p class="text-gray-500">
                                            {{ $order->shipping_address_line_1 }}
                                            @if ($order->shipping_address_line_2)<br>{{ $order->shipping_address_line_2 }}@endif
                                            <br>{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
                                            @if ($order->shipping_country)<br>{{ $order->shipping_country }}@endif
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Items</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($items as $item)
                                            <tr>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                                    {{ $item->product_title }}
                                                    @if ($item->is_digital)
                                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 ml-1">Digital</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-500">{{ $item->quantity }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                                                <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($item->total_cents / 100, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Vendor Split Info --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-brand-primary">Payout Breakdown</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Order Subtotal</span>
                                    <span class="text-sm font-semibold text-gray-900">${{ number_format($split->subtotal_cents / 100, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Platform Fee ({{ number_format($split->platform_percentage, 1) }}%)</span>
                                    <span class="text-sm text-gray-500">-${{ number_format($split->platform_fee_cents / 100, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm font-semibold text-gray-900">Your Payout</span>
                                    <span class="text-lg font-bold text-brand-secondary">${{ number_format($split->vendor_payout_cents / 100, 2) }}</span>
                                </div>
                            </div>
                            @if ($split->shipped_at)
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <p class="text-sm text-gray-500">
                                        Shipped: <span class="font-medium text-gray-700">{{ $split->shipped_at->format('M j, Y \a\t g:i A') }}</span>
                                    </p>
                                    @if ($split->tracking_number)
                                        <p class="text-sm text-gray-500 mt-1">
                                            Tracking: <span class="font-medium font-mono text-gray-700">{{ $split->tracking_number }}</span>
                                        </p>
                                    @endif
                                </div>
                            @endif
                            @if ($split->delivered_at)
                                <p class="text-sm text-gray-500 mt-2">
                                    Delivered: <span class="font-medium text-gray-700">{{ $split->delivered_at->format('M j, Y \a\t g:i A') }}</span>
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sidebar: Actions --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6 space-y-4">
                            <h4 class="text-lg font-semibold mb-4 text-brand-primary">Actions</h4>

                            {{-- Ship Order --}}
                            @if (in_array($splitStatus, ['pending', 'processing']))
                                <form method="POST" action="{{ route('vendor.orders.ship', $split->order) }}" x-data="{ showTracking: false }">
                                    @csrf
                                    <div class="space-y-3">
                                        <button data-guide="vendor-mark-shipped" type="button" @click="showTracking = !showTracking" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white transition bg-brand-primary">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                            Mark as Shipped
                                        </button>

                                        <div x-show="showTracking" x-cloak class="space-y-3">
                                            <div>
                                                <label for="tracking_number" class="block text-sm font-medium text-gray-700">Tracking Number</label>
                                                <input type="text" name="tracking_number" id="tracking_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-opacity-50 sm:text-sm" placeholder="e.g., 1Z999AA10123456784">
                                                <p class="mt-1 text-xs text-gray-400">Optional. Customer will be notified.</p>
                                            </div>
                                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-500 transition">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Confirm Shipment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif

                            {{-- Mark as Delivered --}}
                            @if ($splitStatus === 'shipped')
                                <form method="POST" action="{{ route('vendor.orders.deliver', $split->order) }}" onsubmit="return confirm('Mark this order as delivered?');">
                                    @csrf
                                    <button data-guide="vendor-mark-delivered" type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-green-300 text-sm font-medium rounded-md text-green-700 hover:bg-green-50 transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Mark as Delivered
                                    </button>
                                </form>
                            @endif

                            {{-- Status Indicators --}}
                            @if (in_array($splitStatus, ['delivered', 'canceled', 'refunded']))
                                <div class="p-3 bg-gray-50 rounded-lg text-center">
                                    @if ($splitStatus === 'delivered')
                                        <p class="text-sm font-medium text-green-700">Order Delivered</p>
                                    @elseif ($splitStatus === 'canceled')
                                        <p class="text-sm font-medium text-red-700">Order Canceled</p>
                                    @elseif ($splitStatus === 'refunded')
                                        <p class="text-sm font-medium text-gray-700">Order Refunded</p>
                                    @endif
                                </div>
                            @endif

                            <hr class="my-2">

                            {{-- Back to Orders --}}
                            <a href="{{ route('vendor.orders.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Back to Orders
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
