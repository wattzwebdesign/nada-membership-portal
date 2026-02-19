<x-public-layout>
    <x-slot name="title">Checkout - NADA Shop</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <h1 class="text-2xl font-bold text-gray-900 mb-6">Checkout</h1>

        {{-- Flash Messages --}}
        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('shop.checkout.store') }}">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- Left Column: Checkout Fields --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Customer Information Section --}}
                    @if (isset($checkoutFields['customer']) && $checkoutFields['customer']->isNotEmpty())
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Customer Information</h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($checkoutFields['customer'] as $field)
                                    <div class="{{ str_contains($field->field_name, 'email') || str_contains($field->field_name, 'company') || str_contains($field->field_name, 'phone') ? 'sm:col-span-2' : '' }}">
                                        <label for="{{ $field->field_name }}" class="block text-sm font-medium text-gray-700">
                                            {{ $field->label }}
                                            @if ($field->is_required)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        <input type="{{ str_contains($field->field_name, 'email') ? 'email' : 'text' }}"
                                               name="{{ $field->field_name }}" id="{{ $field->field_name }}"
                                               value="{{ old($field->field_name, auth()->user()->{$field->field_name} ?? '') }}"
                                               {{ $field->is_required ? 'required' : '' }}
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                                        @error($field->field_name) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Billing Address Section --}}
                    @if (isset($checkoutFields['billing']) && $checkoutFields['billing']->isNotEmpty())
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Billing Address</h2>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach ($checkoutFields['billing'] as $field)
                                    <div class="{{ str_contains($field->field_name, 'line_1') || str_contains($field->field_name, 'line_2') ? 'sm:col-span-2' : '' }}">
                                        <label for="{{ $field->field_name }}" class="block text-sm font-medium text-gray-700">
                                            {{ $field->label }}
                                            @if ($field->is_required)
                                                <span class="text-red-500">*</span>
                                            @endif
                                        </label>
                                        <input type="text" name="{{ $field->field_name }}" id="{{ $field->field_name }}"
                                               value="{{ old($field->field_name) }}"
                                               {{ $field->is_required ? 'required' : '' }}
                                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                                        @error($field->field_name) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Shipping Address Section (hidden if all digital) --}}
                    @php
                        $hasPhysicalItems = collect($cart)->contains(fn ($item) => !($item['is_digital'] ?? false));
                    @endphp

                    @if ($hasPhysicalItems && isset($checkoutFields['shipping']) && $checkoutFields['shipping']->isNotEmpty())
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">Shipping Address</h2>
                            </div>

                            <div x-data="{ sameAsBilling: true }">
                                <label class="flex items-center text-sm text-gray-600 mb-4">
                                    <input type="checkbox" x-model="sameAsBilling" name="same_as_billing" value="1" checked
                                           class="rounded border-gray-300 text-brand-primary focus:ring-brand-primary">
                                    <span class="ml-2">Same as billing address</span>
                                </label>

                                <div x-show="!sameAsBilling" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach ($checkoutFields['shipping'] as $field)
                                        <div class="{{ str_contains($field->field_name, 'line_1') || str_contains($field->field_name, 'line_2') ? 'sm:col-span-2' : '' }}">
                                            <label for="{{ $field->field_name }}" class="block text-sm font-medium text-gray-700">
                                                {{ $field->label }}
                                                @if ($field->is_required)
                                                    <span class="text-red-500">*</span>
                                                @endif
                                            </label>
                                            <input type="text" name="{{ $field->field_name }}" id="{{ $field->field_name }}"
                                                   value="{{ old($field->field_name) }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                                            @error($field->field_name) <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Right Column: Order Summary --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h2>

                        {{-- Cart Items --}}
                        <div class="space-y-3 divide-y divide-gray-100">
                            @foreach ($cart as $item)
                                <div class="flex gap-3 {{ !$loop->first ? 'pt-3' : '' }}">
                                    @if (!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="" class="w-12 h-12 rounded object-cover flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $item['title'] }}</p>
                                        <p class="text-xs text-gray-500">Qty: {{ $item['quantity'] }}</p>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">${{ number_format($item['total_cents'] / 100, 2) }}</p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totals --}}
                        <div class="border-t border-gray-200 mt-4 pt-4 space-y-2">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Subtotal</span>
                                <span>${{ number_format($subtotal / 100, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Shipping</span>
                                <span>{{ $shipping === 0 ? 'Free' : '$' . number_format($shipping / 100, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-base font-bold text-gray-900 border-t border-gray-200 pt-2">
                                <span>Total</span>
                                <span>${{ number_format($total / 100, 2) }}</span>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <button type="submit" class="mt-6 w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Proceed to Payment
                        </button>

                        <p class="mt-3 text-xs text-gray-500 text-center">
                            You will be redirected to Stripe to complete your payment securely.
                        </p>

                        {{-- Back to Cart --}}
                        <a href="{{ route('shop.cart.index') }}" class="mt-3 block text-center text-sm text-brand-primary hover:underline">
                            &larr; Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-public-layout>
