@props(['product'])

@if(!$product->track_stock)
    <span class="text-xs text-green-600 font-medium">In Stock</span>
@elseif($product->stock_quantity > 10)
    <span class="text-xs text-green-600 font-medium">In Stock</span>
@elseif($product->stock_quantity > 0)
    <span class="text-xs text-amber-600 font-medium">Low Stock ({{ $product->stock_quantity }} left)</span>
@else
    <span class="text-xs text-red-600 font-medium">Out of Stock</span>
@endif
