<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vendor Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-md">
                    {{ session('warning') }}
                </div>
            @endif

            {{-- Vendor Setup Notice --}}
            @if (!$vendorProfile || !$vendor->hasConnectedStripeAccount())
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-5">
                    <div class="flex items-start gap-3">
                        <svg class="h-6 w-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-amber-800">Complete your Vendor setup</h3>
                            <p class="text-sm text-amber-700 mt-1">You need to complete the following before you can list products and receive orders:</p>
                            <ul class="mt-3 space-y-2">
                                @if (!$vendorProfile)
                                    <li class="flex items-center gap-2 text-sm">
                                        <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span class="text-amber-800">Set up your vendor profile</span>
                                        <a href="{{ route('vendor.profile.edit') }}" class="ml-auto text-xs font-medium px-2.5 py-1 rounded text-white bg-brand-primary">Set Up Profile</a>
                                    </li>
                                @endif
                                @if (!$vendor->hasConnectedStripeAccount())
                                    <li class="flex items-center gap-2 text-sm">
                                        <svg class="h-4 w-4 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span class="text-amber-800">Connect your Stripe account for payouts</span>
                                        <a href="{{ route('vendor.payouts.index') }}" class="ml-auto text-xs font-medium px-2.5 py-1 rounded text-white bg-brand-primary">Connect Stripe</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Stats Row --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-lg bg-brand-primary/10">
                                <svg class="w-6 h-6 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Orders</p>
                                <p class="text-2xl font-bold text-brand-primary">{{ $stats['total_orders'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-lg bg-brand-secondary/10">
                                <svg class="w-6 h-6 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Revenue</p>
                                <p class="text-2xl font-bold text-brand-secondary">${{ number_format(($stats['total_revenue'] ?? 0) / 100, 2) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-lg bg-green-50">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Active Products</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['active_products'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 p-3 rounded-lg bg-blue-50">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Pending Shipments</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $stats['pending_shipments'] ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ route('vendor.profile.edit') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-brand-secondary/10">
                        <svg class="w-5 h-5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Vendor Profile</p>
                </a>

                <a href="{{ route('vendor.products.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-brand-primary/10">
                        <svg class="w-5 h-5 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Products</p>
                </a>

                <a href="{{ route('vendor.orders.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-blue-50">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Orders</p>
                </a>

                <a href="{{ route('vendor.payouts.index') }}" class="flex flex-col items-center p-4 bg-white shadow-sm sm:rounded-lg border border-gray-200 hover:bg-gray-50 transition text-center">
                    <div class="p-2 rounded-lg bg-brand-secondary/10">
                        <svg class="w-5 h-5 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="mt-2 text-xs font-medium text-gray-900">Payouts</p>
                </a>
            </div>

            {{-- Recent Orders --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-brand-primary">Recent Orders</h3>
                        <a href="{{ route('vendor.orders.index') }}" class="text-sm font-medium hover:underline text-brand-secondary">View All</a>
                    </div>

                    @if (isset($recentOrders) && $recentOrders->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order #</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($recentOrders as $split)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-brand-primary">
                                                <a href="{{ route('vendor.orders.show', $split->order) }}" class="hover:underline">#{{ $split->order->order_number }}</a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $split->order->user->full_name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($split->vendor_amount / 100, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
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
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $splitStatusColor }}">
                                                    {{ ucfirst($splitStatus) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $split->order->created_at->format('M j, Y') }}</td>
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
                            @foreach ($recentOrders as $split)
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
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $splitStatusColor }}">
                                            {{ ucfirst($splitStatus) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $split->order->user->full_name ?? 'N/A' }} | {{ $split->order->created_at->format('M j, Y') }}</p>
                                    <p class="text-sm font-semibold text-gray-900 mt-1">${{ number_format($split->vendor_amount / 100, 2) }}</p>
                                    <a href="{{ route('vendor.orders.show', $split->order) }}" class="mt-2 inline-block text-xs font-medium text-brand-primary">View Details</a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <p class="mt-2 text-sm text-gray-500">No orders yet.</p>
                            <p class="text-xs text-gray-400 mt-1">Orders will appear here once customers purchase your products.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
