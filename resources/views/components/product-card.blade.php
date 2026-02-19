@props(['product'])

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
    <a href="{{ route('public.shop.show', $product) }}">
        @if($product->featured_image_url)
            <img src="{{ $product->featured_image_url }}" alt="{{ $product->title }}" class="w-full h-48 object-cover" />
        @else
            <div class="w-full h-48 bg-gray-100 flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        @endif
    </a>

    <div class="p-4">
        <a href="{{ route('public.shop.show', $product) }}" class="block">
            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $product->title }}</h3>
        </a>

        <a href="{{ route('public.shop.vendor', $product->vendorProfile) }}" class="text-xs text-gray-500 hover:text-brand-primary mt-1 block">
            {{ $product->vendorProfile->business_name }}
        </a>

        <div class="mt-2">
            <x-price-display :product="$product" />
        </div>

        <div class="mt-2 flex items-center justify-between">
            <x-stock-indicator :product="$product" />
            @if($product->is_digital)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Digital</span>
            @elseif($product->getShippingFeeCents() === 0)
                <span class="text-xs text-green-600">Free Shipping</span>
            @else
                <span class="text-xs text-gray-500">+ {{ $product->shipping_fee_formatted }} shipping</span>
            @endif
        </div>
    </div>
</div>
