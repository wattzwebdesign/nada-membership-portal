<x-public-layout>
    <x-slot name="title">{{ $product->title }} - NADA Shop</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Back Link --}}
        <a href="{{ route('public.shop.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Shop
        </a>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 border border-green-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-md bg-red-50 border border-red-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">

            {{-- Left Column: Gallery --}}
            <div x-data="{ activeImage: 0 }">
                @php
                    $images = $product->getMedia('images');
                @endphp

                {{-- Main Image --}}
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                    @if ($images->isNotEmpty())
                        @foreach ($images as $index => $image)
                            <img x-show="activeImage === {{ $index }}"
                                 src="{{ $image->hasGeneratedConversion('webp') ? $image->getUrl('webp') : $image->getUrl() }}" alt="{{ $product->title }}"
                                 class="w-full h-full object-cover">
                        @endforeach
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                    @endif
                </div>

                {{-- Thumbnail Strip --}}
                @if ($images->count() > 1)
                    <div class="mt-4 grid grid-cols-5 gap-2">
                        @foreach ($images as $index => $image)
                            <button @click="activeImage = {{ $index }}"
                                    :class="activeImage === {{ $index }} ? 'ring-2 ring-brand-primary' : 'ring-1 ring-gray-200 hover:ring-gray-300'"
                                    class="aspect-square rounded-md overflow-hidden focus:outline-none transition-all">
                                <img src="{{ $image->hasGeneratedConversion('thumb') ? $image->getUrl('thumb') : $image->getUrl() }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Right Column: Product Details --}}
            <div>
                {{-- Category --}}
                @if ($product->category)
                    <a href="{{ route('public.shop.index', ['category' => $product->category->slug]) }}" class="text-xs font-medium text-brand-secondary uppercase tracking-wider hover:underline">
                        {{ $product->category->name }}
                    </a>
                @endif

                {{-- Title --}}
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 mt-1">{{ $product->title }}</h1>

                {{-- Vendor --}}
                @if ($product->vendorProfile)
                    <p class="mt-2 text-sm text-gray-500">
                        Sold by
                        <a href="{{ route('public.shop.vendor', $product->vendorProfile) }}" class="font-medium text-brand-primary hover:underline">
                            {{ $product->vendorProfile->business_name }}
                        </a>
                    </p>
                @endif

                {{-- Pricing --}}
                <div class="mt-4 pb-4 border-b border-gray-200">
                    @php
                        $showMemberPrice = auth()->check() && auth()->user()->hasFullMembership() && $product->member_price_cents;
                        $effectivePrice = $showMemberPrice ? $product->member_price_cents : $product->price_cents;
                    @endphp

                    <div class="flex items-baseline gap-3">
                        <span class="text-3xl font-bold text-brand-primary">${{ number_format($effectivePrice / 100, 2) }}</span>

                        @if ($showMemberPrice && $product->member_price_cents < $product->price_cents)
                            <span class="text-lg text-gray-400 line-through">{{ $product->price_formatted }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-brand-secondary text-white">Member Price</span>
                        @elseif (!auth()->check() && $product->member_price_cents && $product->member_price_cents < $product->price_cents)
                            <span class="text-sm text-brand-secondary font-medium">Member Price: {{ $product->member_price_formatted }}</span>
                        @endif
                    </div>
                </div>

                {{-- Stock Status --}}
                <div class="mt-4">
                    @if (!$product->isInStock())
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Out of Stock
                        </span>
                    @elseif ($product->track_stock && $product->stock_quantity <= 5)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Only {{ $product->stock_quantity }} left in stock
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            In Stock
                        </span>
                    @endif
                </div>

                {{-- Add to Cart --}}
                @if ($product->isInStock())
                    <form method="POST" action="{{ route('shop.cart.add') }}" class="mt-6">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">

                        <div class="flex items-center gap-4">
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <select name="quantity" id="quantity" class="rounded-md border-gray-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary text-sm">
                                    @for ($i = 1; $i <= min($product->track_stock ? $product->stock_quantity : 10, 10); $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="flex-1 pt-5">
                                <button type="submit" class="w-full inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-primary hover:bg-brand-primary-hover transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </form>
                @endif

                {{-- Shipping Info --}}
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <div class="flex items-center text-sm text-gray-600">
                        @if ($product->is_digital)
                            <svg class="w-5 h-5 mr-2 text-brand-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Digital product -- instant download after purchase</span>
                        @else
                            <svg class="w-5 h-5 mr-2 text-brand-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            <span>Shipping: {{ $product->shipping_fee_formatted }}</span>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                @if ($product->description)
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Description</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Vendor Info Card --}}
                @if ($product->vendorProfile)
                    <div class="mt-6 border-t border-gray-200 pt-6">
                        <a href="{{ route('public.shop.vendor', $product->vendorProfile) }}" class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            @if ($product->vendorProfile->logo_url)
                                <img src="{{ $product->vendorProfile->logo_url }}" alt="{{ $product->vendorProfile->business_name }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                            @else
                                <div class="w-12 h-12 rounded-full bg-brand-primary flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-sm font-bold">{{ strtoupper(substr($product->vendorProfile->business_name, 0, 2)) }}</span>
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $product->vendorProfile->business_name }}</p>
                                <p class="text-xs text-brand-primary font-medium">View all products &rarr;</p>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-public-layout>
