<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartIcon extends Component
{
    public int $count = 0;

    public function mount(): void
    {
        $this->count = app(CartService::class)->getItemCount();
    }

    #[On('cart-updated')]
    public function updateCount(): void
    {
        $this->count = app(CartService::class)->getItemCount();
    }

    public function render()
    {
        return view('livewire.cart-icon');
    }
}
