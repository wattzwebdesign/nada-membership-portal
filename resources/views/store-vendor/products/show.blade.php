<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $product->title }}
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

            @php
                $productStatus = is_object($product->status) ? $product->status->value : $product->status;
                $productStatusColors = [
                    'draft' => 'bg-gray-100 text-gray-800',
                    'active' => 'bg-green-100 text-green-800',
                    'inactive' => 'bg-red-100 text-red-800',
                ];
                $productStatusColor = $productStatusColors[$productStatus] ?? 'bg-gray-100 text-gray-800';
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Product Images --}}
                    @if ($product->images && count($product->images) > 0)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach ($product->images as $image)
                                        <img src="{{ $image->url }}" alt="{{ $product->title }}" class="w-full h-48 rounded-lg object-cover border border-gray-200">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Product Details --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            {{-- Badges --}}
                            <div class="flex flex-wrap gap-2 mb-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $productStatusColor }}">
                                    {{ ucfirst($productStatus) }}
                                </span>
                                @if ($product->is_digital)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Digital</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Physical</span>
                                @endif
                                @if ($product->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-brand-primary/10 text-brand-primary">{{ $product->category->name }}</span>
                                @endif
                            </div>

                            <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $product->title }}</h3>

                            @if ($product->description)
                                <div class="mb-6">
                                    <div class="prose max-w-none text-gray-600">
                                        {!! nl2br(e($product->description)) !!}
                                    </div>
                                </div>
                            @endif

                            {{-- Details Grid --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-200 pt-6">
                                {{-- Price --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Price</p>
                                        <p class="text-sm text-gray-500">${{ number_format($product->price / 100, 2) }}</p>
                                        @if ($product->member_price)
                                            <p class="text-sm text-brand-secondary">Member Price: ${{ number_format($product->member_price / 100, 2) }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Stock --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Stock</p>
                                        @if ($product->track_stock)
                                            <p class="text-sm text-gray-500">{{ $product->stock_quantity }} in stock</p>
                                        @else
                                            <p class="text-sm text-gray-400">Not tracking</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- SKU --}}
                                @if ($product->sku)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">SKU</p>
                                            <p class="text-sm text-gray-500 font-mono">{{ $product->sku }}</p>
                                        </div>
                                    </div>
                                @endif

                                {{-- Shipping Fee --}}
                                @if (!$product->is_digital)
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">Shipping Fee</p>
                                            @if ($product->shipping_fee)
                                                <p class="text-sm text-gray-500">${{ number_format($product->shipping_fee / 100, 2) }}</p>
                                            @else
                                                <p class="text-sm text-gray-400">Using default</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Orders --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Orders</p>
                                        <p class="text-sm text-gray-500">{{ $product->orders_count ?? 0 }} total orders</p>
                                    </div>
                                </div>

                                {{-- Created --}}
                                <div class="flex items-start">
                                    <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0 text-brand-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Created</p>
                                        <p class="text-sm text-gray-500">{{ $product->created_at->format('M j, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar: Actions --}}
                <div class="lg:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6 space-y-3">
                            <h4 class="text-lg font-semibold mb-4 text-brand-primary">Actions</h4>

                            {{-- Edit Product --}}
                            <a href="{{ route('vendor.products.edit', $product) }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-md text-white transition bg-brand-primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit Product
                            </a>

                            {{-- Delete Product --}}
                            <form method="POST" action="{{ route('vendor.products.destroy', $product) }}" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 border border-red-300 text-sm font-medium rounded-md text-red-700 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Delete Product
                                </button>
                            </form>

                            <hr class="my-2">

                            {{-- Back to Products --}}
                            <a href="{{ route('vendor.products.index') }}" class="w-full inline-flex justify-center items-center px-4 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
