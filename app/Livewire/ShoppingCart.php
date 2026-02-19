<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

class ShoppingCart extends Component
{
    public array $items = [];

    public function mount(): void
    {
        $this->refreshItems();
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        app(CartService::class)->updateQuantity($key, $quantity);
        $this->refreshItems();
        $this->dispatch('cart-updated');
    }

    public function removeItem(string $key): void
    {
        app(CartService::class)->removeItem($key);
        $this->refreshItems();
        $this->dispatch('cart-updated');
    }

    public function refreshItems(): void
    {
        $cartService = app(CartService::class);
        $this->items = $cartService->getItems();
    }

    public function getSubtotalProperty(): int
    {
        $user = auth()->user();
        return app(CartService::class)->getSubtotalCentsForUser($user);
    }

    public function getShippingProperty(): int
    {
        return app(CartService::class)->getShippingCents();
    }

    public function getTotalProperty(): int
    {
        return $this->subtotal + $this->shipping;
    }

    public function getItemCountProperty(): int
    {
        return app(CartService::class)->getItemCount();
    }

    public function render()
    {
        return view('livewire.shopping-cart');
    }
}
