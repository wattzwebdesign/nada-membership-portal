<?php

namespace App\Services;

use App\Models\Product;

class CartService
{
    protected const SESSION_KEY = 'shop_cart';

    public function getItems(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function addItem(Product $product, int $quantity = 1): void
    {
        $items = $this->getItems();
        $key = (string) $product->id;

        if (isset($items[$key])) {
            $items[$key]['quantity'] += $quantity;
        } else {
            $items[$key] = [
                'product_id' => $product->id,
                'title' => $product->title,
                'price_cents' => $product->price_cents,
                'member_price_cents' => $product->member_price_cents,
                'shipping_fee_cents' => $product->getShippingFeeCents(),
                'is_digital' => $product->is_digital,
                'vendor_profile_id' => $product->vendor_profile_id,
                'quantity' => $quantity,
                'image_url' => $product->featured_image_url,
            ];
        }

        session([self::SESSION_KEY => $items]);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $items = $this->getItems();

        if (isset($items[$key])) {
            if ($quantity <= 0) {
                unset($items[$key]);
            } else {
                $items[$key]['quantity'] = $quantity;
            }
        }

        session([self::SESSION_KEY => $items]);
    }

    public function removeItem(string $key): void
    {
        $items = $this->getItems();
        unset($items[$key]);
        session([self::SESSION_KEY => $items]);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function getSubtotalCents(): int
    {
        $items = $this->getItems();
        $total = 0;

        foreach ($items as $item) {
            $price = $item['price_cents'];
            $total += $price * $item['quantity'];
        }

        return $total;
    }

    public function getSubtotalCentsForUser(?\App\Models\User $user): int
    {
        $items = $this->getItems();
        $total = 0;

        foreach ($items as $item) {
            $price = $item['price_cents'];
            if ($user && $item['member_price_cents'] && $user->hasFullMembership()) {
                $price = $item['member_price_cents'];
            }
            $total += $price * $item['quantity'];
        }

        return $total;
    }

    public function getShippingCents(): int
    {
        $items = $this->getItems();
        $total = 0;

        foreach ($items as $item) {
            if (! $item['is_digital']) {
                $total += $item['shipping_fee_cents'] * $item['quantity'];
            }
        }

        return $total;
    }

    public function getItemCount(): int
    {
        $items = $this->getItems();
        $count = 0;

        foreach ($items as $item) {
            $count += $item['quantity'];
        }

        return $count;
    }

    public function hasOnlyDigitalItems(): bool
    {
        $items = $this->getItems();

        foreach ($items as $item) {
            if (! $item['is_digital']) {
                return false;
            }
        }

        return count($items) > 0;
    }

    public function validateStock(): array
    {
        $errors = [];
        $items = $this->getItems();

        foreach ($items as $key => $item) {
            $product = Product::find($item['product_id']);

            if (! $product || $product->status->value !== 'active') {
                $errors[$key] = "{$item['title']} is no longer available.";
                continue;
            }

            if ($product->track_stock && $product->stock_quantity < $item['quantity']) {
                $errors[$key] = "{$item['title']} only has {$product->stock_quantity} in stock.";
            }
        }

        return $errors;
    }
}
