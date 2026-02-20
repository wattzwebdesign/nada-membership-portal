<x-public-layout>
    <x-slot name="title">{{ $vendorProfile->business_name }} - NADA Shop</x-slot>

    {{-- Vendor Header --}}
    <div class="bg-brand-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                {{-- Logo --}}
                @if ($vendorProfile->logo_url)
                    <img src="{{ $vendorProfile->logo_url }}" alt="{{ $vendorProfile->business_name }}"
                         class="w-24 h-24 rounded-full object-cover border-4 border-white/20 flex-shrink-0">
                @else
                    <div class="w-24 h-24 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0 border-4 border-white/20">
                        <span class="text-white text-2xl font-bold">{{ strtoupper(substr($vendorProfile->business_name, 0, 2)) }}</span>
                    </div>
                @endif

                {{-- Vendor Info --}}
                <div class="text-center sm:text-left">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $vendorProfile->business_name }}</h1>
                    @if ($vendorProfile->description)
                        <p class="mt-2 text-gray-300 max-w-2xl">{{ $vendorProfile->description }}</p>
                    @endif
                    <div class="mt-3 flex flex-wrap justify-center sm:justify-start gap-3">
                        @if ($vendorProfile->website)
                            <a href="{{ $vendorProfile->website }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-sm text-gray-300 hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                Website
                            </a>
                        @endif
                        @if ($vendorProfile->email)
                            <a href="mailto:{{ $vendorProfile->email }}" class="inline-flex items-center text-sm text-gray-300 hover:text-white transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Contact
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mb-6 rounded-md bg-green-50 border border-green-200 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Gallery --}}
        @php
            $galleryImages = $vendorProfile->getMedia('gallery');
        @endphp
        @if ($galleryImages->isNotEmpty())
            <div class="mb-8">
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    @foreach ($galleryImages as $image)
                        <div class="aspect-square rounded-lg overflow-hidden bg-gray-100">
                            <img src="{{ $image->hasGeneratedConversion('webp') ? $image->getUrl('webp') : $image->getUrl() }}" alt="" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Products Heading --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">Products</h2>
            <p class="text-sm text-gray-500">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>
        </div>

        {{-- Product Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse ($products as $product)
                <a href="{{ route('public.shop.show', $product) }}" class="group bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all duration-150">
                    {{-- Product Image --}}
                    <div class="aspect-square bg-gray-100 overflow-hidden">
                        @if ($product->featured_image_url)
                            <img src="{{ $product->featured_image_url }}" alt="{{ $product->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Product Info --}}
                    <div class="p-4">
                        <h3 class="text-sm font-semibold text-gray-900 line-clamp-2 group-hover:text-brand-primary transition-colors">{{ $product->title }}</h3>

                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-lg font-bold text-brand-primary">{{ $product->price_formatted }}</span>
                            @if ($product->member_price_cents && $product->member_price_cents < $product->price_cents)
                                <span class="text-xs font-medium text-brand-secondary">Member: {{ $product->member_price_formatted }}</span>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 mt-1.5">
                            @if ($product->is_digital)
                                <span class="inline-flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Digital Download
                                </span>
                            @else
                                Shipping: {{ $product->shipping_fee_formatted }}
                            @endif
                        </p>

                        @if ($product->track_stock && $product->stock_quantity <= 0)
                            <span class="inline-flex items-center mt-2 px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">Out of Stock</span>
                        @endif
                    </div>
                </a>
            @empty
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">This vendor doesn't have any products listed yet.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($products->hasPages())
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-public-layout>
