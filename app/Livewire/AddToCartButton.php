<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class AddToCartButton extends Component
{
    public int $productId;
    public int $quantity = 1;
    public bool $added = false;

    public function mount(int $productId): void
    {
        $this->productId = $productId;
    }

    public function addToCart(): void
    {
        $product = Product::active()->findOrFail($this->productId);

        if ($product->track_stock && $product->stock_quantity < $this->quantity) {
            session()->flash('error', 'Not enough stock available.');
            return;
        }

        app(CartService::class)->addItem($product, $this->quantity);

        $this->added = true;
        $this->dispatch('cart-updated');

        // Reset after 2 seconds
        $this->js("setTimeout(() => { \$wire.set('added', false) }, 2000)");
    }

    public function render()
    {
        return view('livewire.add-to-cart-button');
    }
}
