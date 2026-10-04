<?php

namespace App\Livewire;

use App\Models\Order;
use App\Support\Cart;
use App\Support\OrderNotifier;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CartPage extends Component
{
    #[Validate('required|string|max:100', as: 'имя')]
    public string $name = '';

    #[Validate('required|string|min:10|max:30', as: 'телефон')]
    public string $phone = '';

    #[Validate('nullable|email|max:150', as: 'e-mail')]
    public string $email = '';

    #[Validate('required|in:pickup,delivery')]
    public string $delivery = 'pickup';

    #[Validate('required_if:delivery,delivery|nullable|string|max:255', as: 'адрес')]
    public string $address = '';

    #[Validate('nullable|date|after_or_equal:today', as: 'дата')]
    public ?string $readyDate = null;

    #[Validate('nullable|string|max:1000')]
    public string $comment = '';

    public ?int $orderId = null;

    public function setQty(int $productId, int $qty): void
    {
        Cart::set($productId, $qty);
        $this->dispatch('cart-updated');
    }

    public function remove(int $productId): void
    {
        Cart::set($productId, 0);
        $this->dispatch('cart-updated');
    }

    public function checkout(): void
    {
        $this->validate();

        $lines = Cart::lines();
        if ($lines->isEmpty()) {
            return;
        }

        $order = Order::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email ?: null,
            'delivery' => $this->delivery,
            'address' => $this->delivery === 'delivery' ? $this->address : null,
            'ready_date' => $this->readyDate ?: null,
            'comment' => $this->comment ?: null,
            'items' => $lines->map(fn ($l) => [
                'id' => $l['product']->id,
                'name' => $l['product']->name,
                'price' => $l['product']->price,
                'qty' => $l['qty'],
                'sum' => $l['sum'],
            ])->all(),
            'total' => $lines->sum('sum'),
        ]);

        OrderNotifier::send($order);

        Cart::clear();
        $this->orderId = $order->id;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $lines = Cart::lines();

        return view('livewire.cart-page', [
            'lines' => $lines,
            'total' => $lines->sum('sum'),
        ])->title('Корзина');
    }
}
