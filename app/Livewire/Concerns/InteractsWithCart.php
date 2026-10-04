<?php

namespace App\Livewire\Concerns;

use App\Models\Product;
use App\Support\Cart;

trait InteractsWithCart
{
    public function addToCart(int $productId, int $qty = 1): void
    {
        $product = Product::visible()->findOrFail($productId);
        Cart::add($product->id, $qty);
        $this->dispatch('cart-updated', name: $product->name);
    }
}
