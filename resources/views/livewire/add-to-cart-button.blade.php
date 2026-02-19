<div class="flex items-center gap-3">
    <div class="flex items-center border border-gray-300 rounded-md">
        <button wire:click="$set('quantity', Math.max(1, quantity - 1))" type="button" class="px-3 py-2 text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
        </button>
        <input type="number" wire:model="quantity" min="1" max="99" class="w-14 text-center border-0 focus:ring-0 text-sm" />
        <button wire:click="$set('quantity', quantity + 1)" type="button" class="px-3 py-2 text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>

    <button
        wire:click="addToCart"
        wire:loading.attr="disabled"
        class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md text-white {{ $added ? 'bg-green-600' : 'bg-brand-primary hover:bg-brand-accent' }} transition-colors"
    >
        <span wire:loading.remove wire:target="addToCart">
            @if($added)
                <svg class="w-5 h-5 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Added!
            @else
                Add to Cart
            @endif
        </span>
        <span wire:loading wire:target="addToCart">Adding...</span>
    </button>
</div>
