<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Order') }} #{{ $order->order_number }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Order Details --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-brand-primary">Order Details</h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColor }}">
                                    {{ ucfirst($statusValue) }}
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
                                @if ($order->paid_at)
                                    <div>
                                        <p class="font-medium text-gray-900">Paid</p>
                                        <p class="text-gray-500">{{ $order->paid_at->format('M j, Y \a\t g:i A') }}</p>
                                    </div>
                                @endif
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

                            {{-- Desktop Table --}}
                            <div class="hidden sm:block overflow-x-auto">
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
                                        @foreach ($order->items as $item)
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

                            {{-- Mobile Cards --}}
                            <div class="sm:hidden space-y-3">
                                @foreach ($order->items as $item)
                                    <div class="border border-gray-100 rounded-lg p-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $item->product_title }}
                                            @if ($item->is_digital)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 ml-1">Digital</span>
                                            @endif
                                        </p>
                                        <div class="flex items-center justify-between mt-1 text-sm text-gray-500">
                                            <span>Qty: {{ $item->quantity }}</span>
                                            <span class="font-medium text-gray-900">${{ number_format($item->total_cents / 100, 2) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Tracking Info --}}
                    @if ($order->vendorOrderSplits->contains(fn ($s) => $s->shipped_at || $s->tracking_number))
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4 text-brand-primary">Shipping & Tracking</h3>
                                <div class="space-y-3">
                                    @foreach ($order->vendorOrderSplits as $split)
                                        @if ($split->shipped_at)
                                            <div class="border border-gray-100 rounded-lg p-3">
                                                <p class="text-sm text-gray-500">
                                                    Shipped: <span class="font-medium text-gray-700">{{ $split->shipped_at->format('M j, Y') }}</span>
                                                </p>
                                                @if ($split->tracking_number)
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        Tracking: <span class="font-medium font-mono text-gray-700">{{ $split->tracking_number }}</span>
                                                    </p>
                                                @endif
                                                @if ($split->delivered_at)
                                                    <p class="text-sm text-gray-500 mt-1">
                                                        Delivered: <span class="font-medium text-gray-700">{{ $split->delivered_at->format('M j, Y') }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Digital Downloads --}}
                    @if ($order->items->where('is_digital', true)->count() > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-4 text-brand-primary">Digital Downloads</h3>
                                <div class="space-y-3">
                                    @foreach ($order->items->where('is_digital', true) as $item)
                                        <div class="flex items-center justify-between border border-gray-100 rounded-lg p-3">
                                            <span class="text-sm font-medium text-gray-900">{{ $item->product_title }}</span>
                                            <a href="{{ route('shop.download', [$order, $item]) }}" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-brand-primary">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                Download
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- Order Summary --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6 space-y-4">
                            <h4 class="text-lg font-semibold text-brand-primary">Order Summary</h4>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Subtotal</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $order->subtotal_formatted }}</span>
                                </div>
                                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                    <span class="text-sm text-gray-500">Shipping</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $order->shipping_formatted }}</span>
                                </div>
                                @if ($order->tax_cents > 0)
                                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                                        <span class="text-sm text-gray-500">Tax</span>
                                        <span class="text-sm font-medium text-gray-900">${{ number_format($order->tax_cents / 100, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between py-2">
                                    <span class="text-sm font-semibold text-gray-900">Total</span>
                                    <span class="text-lg font-bold text-brand-secondary">{{ $order->total_formatted }}</span>
                                </div>
                            </div>

                            <hr class="my-2">

                            <a href="{{ route('orders.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Back to Orders
                            </a>
                        </div>
                    </div>

                    {{-- Contact Vendor --}}
                    @if (in_array($order->status, [\App\Enums\OrderStatus::Paid, \App\Enums\OrderStatus::Processing, \App\Enums\OrderStatus::Shipped, \App\Enums\OrderStatus::Delivered]))
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-brand-primary mb-2">Need Help?</h4>

                                @php
                                    $vendorNames = $order->vendorOrderSplits
                                        ->pluck('vendorProfile')
                                        ->filter()
                                        ->pluck('business_name')
                                        ->unique()
                                        ->values();
                                @endphp

                                @if ($vendorNames->isNotEmpty())
                                    <p class="text-sm text-gray-500 mb-4">
                                        Contact {{ $vendorNames->join(', ', ' & ') }} about this order.
                                    </p>
                                @endif

                                @if (session('success'))
                                    <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-3">
                                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                                    </div>
                                @endif

                                <form action="{{ route('orders.contact', $order) }}" method="POST">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                        <select id="contact-subject" name="subject" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-accent focus:ring-brand-accent" required>
                                            <option value="">Select a topic...</option>
                                            <option value="Shipping question" @selected(old('subject') === 'Shipping question')>Shipping question</option>
                                            <option value="Item issue" @selected(old('subject') === 'Item issue')>Item issue</option>
                                            <option value="Return / exchange" @selected(old('subject') === 'Return / exchange')>Return / exchange</option>
                                            <option value="Other" @selected(old('subject') === 'Other')>Other</option>
                                        </select>
                                        @error('subject')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                        <textarea id="contact-message" name="message" rows="4" maxlength="2000" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-brand-accent focus:ring-brand-accent" placeholder="Describe your question or issue..." required>{{ old('message') }}</textarea>
                                        @error('message')
                                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-white bg-brand-primary hover:bg-brand-accent rounded-md transition">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        Send Message
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
