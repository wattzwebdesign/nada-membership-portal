<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopCartController extends Controller
{
    public function __construct(
        protected CartService $cartService,
    ) {}

    public function index(): View
    {
        return view('public.shop.cart');
    }

    public function add(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['integer', 'min:1', 'max:99'],
        ]);

        $product = Product::active()->findOrFail($validated['product_id']);

        if ($product->track_stock && $product->stock_quantity < ($validated['quantity'] ?? 1)) {
            return back()->with('error', 'Not enough stock available.');
        }

        $this->cartService->addItem($product, $validated['quantity'] ?? 1);

        return back()->with('success', "{$product->title} added to cart.");
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cartService->updateQuantity($key, $validated['quantity']);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(string $key): RedirectResponse
    {
        $this->cartService->removeItem($key);

        return back()->with('success', 'Item removed from cart.');
    }
}
