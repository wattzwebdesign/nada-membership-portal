@props(['product'])

<div class="flex items-center gap-2">
    <span class="text-lg font-bold text-gray-900">{{ $product->price_formatted }}</span>

    @if($product->member_price_cents)
        <span class="text-sm font-medium text-brand-secondary">
            {{ $product->member_price_formatted }} <span class="text-xs">member</span>
        </span>
    @endif
</div>
