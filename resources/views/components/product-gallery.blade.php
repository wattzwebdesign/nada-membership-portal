@props(['product'])

@php
    $images = $product->getMedia('images');
@endphp

<div x-data="{ activeImage: 0 }" class="space-y-4">
    {{-- Main image --}}
    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
        @if($images->count() > 0)
            @foreach($images as $index => $image)
                <img
                    x-show="activeImage === {{ $index }}"
                    src="{{ $image->hasGeneratedConversion('webp') ? $image->getUrl('webp') : $image->getUrl() }}"
                    alt="{{ $product->title }}"
                    class="w-full h-full object-cover"
                />
            @endforeach
        @else
            <div class="w-full h-full flex items-center justify-center">
                <svg class="w-24 h-24 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        @endif
    </div>

    {{-- Thumbnails --}}
    @if($images->count() > 1)
        <div class="flex gap-2 overflow-x-auto">
            @foreach($images as $index => $image)
                <button
                    @click="activeImage = {{ $index }}"
                    :class="activeImage === {{ $index }} ? 'ring-2 ring-brand-primary' : 'ring-1 ring-gray-200'"
                    class="flex-shrink-0 w-16 h-16 rounded-md overflow-hidden focus:outline-none"
                >
                    <img src="{{ $image->hasGeneratedConversion('thumb') ? $image->getUrl('thumb') : $image->getUrl() }}" alt="" class="w-full h-full object-cover" />
                </button>
            @endforeach
        </div>
    @endif
</div>
