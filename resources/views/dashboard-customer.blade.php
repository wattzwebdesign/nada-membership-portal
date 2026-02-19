<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Recent Orders --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-brand-primary">Recent Orders</h3>
                        @if ($recentOrders->count() > 0)
                            <a href="{{ route('orders.index') }}" class="text-sm font-medium hover:underline text-brand-secondary">View All</a>
                        @endif
                    </div>

                    @if ($recentOrders->count() > 0)
                        <div class="space-y-3">
                            @foreach ($recentOrders as $order)
                                @php
                                    $statusColors = [
                                        'paid' => 'bg-green-100 text-green-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'shipped' => 'bg-indigo-100 text-indigo-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'canceled' => 'bg-red-100 text-red-800',
                                        'refunded' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $statusValue = $order->status->value ?? $order->status;
                                    $statusColor = $statusColors[$statusValue] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <a href="{{ route('orders.show', $order) }}" class="block border border-gray-100 rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">#{{ $order->order_number }}</p>
                                            <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->format('M j, Y') }} | {{ $order->items->count() }} item{{ $order->items->count() !== 1 ? 's' : '' }}</p>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-semibold text-gray-900">{{ $order->total_formatted }}</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                {{ ucfirst($statusValue) }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <p class="mt-2 text-sm text-gray-500">No orders yet.</p>
                            <p class="text-xs text-gray-400">Your purchase history will appear here.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions & Upgrade CTA --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Quick Actions --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-brand-primary">Quick Actions</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <a href="{{ route('public.shop.index') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition">
                                <svg class="w-6 h-6 mb-2 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                <span class="text-sm font-medium text-gray-700">Browse the Shop</span>
                            </a>
                            <a href="{{ route('orders.index') }}" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:border-gray-300 hover:bg-gray-50 transition">
                                <svg class="w-6 h-6 mb-2 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                <span class="text-sm font-medium text-gray-700">View All Orders</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Upgrade CTA --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-brand-secondary/20">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-2 text-brand-primary">Become a NADA Member</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Unlock access to trainings, certificates, clinical submissions, and more by joining NADA as a member.
                        </p>
                        <a href="{{ route('membership.plans') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary">
                            View Membership Plans
                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
