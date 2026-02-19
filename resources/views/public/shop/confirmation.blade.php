<x-public-layout>
    <x-slot name="title">Order Confirmed - NADA Shop</x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Success Header --}}
        <div class="text-center mb-8">
            <svg class="w-20 h-20 mx-auto text-green-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Thank You for Your Order!</h1>
            <p class="mt-2 text-gray-600">Your order has been confirmed and is being processed.</p>
        </div>

        {{-- Order Details Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

            {{-- Order Header --}}
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div>
                        <p class="text-sm text-gray-500">Order Number</p>
                        <p class="text-lg font-bold text-brand-primary">{{ $order->order_number }}</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        <p>Placed on {{ $order->created_at->format('F j, Y \a\t g:i A') }}</p>
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="px-6 py-4">
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Items Ordered</h2>
                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="flex items-start gap-4 py-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ $item->product_title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Qty: {{ $item->quantity }}
                                    @if ($item->was_member_price)
                                        <span class="inline-flex items-center ml-2 px-1.5 py-0.5 rounded text-xs font-medium bg-brand-secondary/10 text-brand-secondary">Member Price</span>
                                    @endif
                                </p>
                                @if ($item->is_digital)
                                    <span class="inline-flex items-center text-xs text-gray-500 mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        Digital Download
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ $item->total_formatted }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Order Totals --}}
            <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
                <div class="space-y-1">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span>{{ $order->subtotal_formatted }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Shipping</span>
                        <span>{{ $order->shipping_formatted }}</span>
                    </div>
                    @if ($order->tax_cents > 0)
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tax</span>
                            <span>${{ number_format($order->tax_cents / 100, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-200 pt-2">
                        <span>Total</span>
                        <span>{{ $order->total_formatted }}</span>
                    </div>
                </div>
            </div>

            {{-- Digital Downloads --}}
            @if ($order->hasDigitalItems())
                <div class="border-t border-gray-200 px-6 py-4">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Digital Downloads</h2>
                    <div class="space-y-2">
                        @foreach ($order->items as $item)
                            @if ($item->is_digital && $item->product)
                                <a href="{{ route('shop.download', ['order' => $order, 'orderItem' => $item]) }}"
                                   class="inline-flex items-center px-4 py-2 border border-brand-primary text-sm font-medium rounded-md text-brand-primary hover:bg-brand-primary hover:text-white transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download {{ $item->product_title }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Download links have also been sent to your email.</p>
                </div>
            @endif

            {{-- Shipping Address --}}
            @if ($order->hasPhysicalItems() && $order->shipping_address_line_1)
                <div class="border-t border-gray-200 px-6 py-4">
                    <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-2">Shipping Address</h2>
                    <div class="text-sm text-gray-600">
                        <p>{{ $order->customer_full_name }}</p>
                        <p>{{ $order->shipping_address_line_1 }}</p>
                        @if ($order->shipping_address_line_2)
                            <p>{{ $order->shipping_address_line_2 }}</p>
                        @endif
                        <p>{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}</p>
                        @if ($order->shipping_country && $order->shipping_country !== 'US')
                            <p>{{ $order->shipping_country }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Confirmation Email Notice --}}
        <div class="mt-6 rounded-md bg-blue-50 border border-blue-200 p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                <p class="ml-3 text-sm text-blue-700">
                    A confirmation email has been sent to <strong>{{ $order->customer_email }}</strong> with your order details.
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('public.shop.index') }}" class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover transition-colors">
                Continue Shopping
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                    Go to Dashboard
                </a>
            @endauth
        </div>
    </div>
</x-public-layout>
