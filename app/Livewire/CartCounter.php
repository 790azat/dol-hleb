<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

class CartCounter extends Component
{
    #[On('cart-updated')]
    public function refresh(): void {}

    public function render()
    {
        return view('livewire.cart-counter', ['count' => Cart::count()]);
    }
}
