<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Product;
use Livewire\Component;

class ProductPage extends Component
{
    use InteractsWithCart;

    public Product $product;

    public int $qty = 1;

    public function mount(string $slug): void
    {
        $this->product = Product::visible()->where('slug', $slug)->with('categories.parent')->firstOrFail();
    }

    public function increment(): void
    {
        $this->qty = min(99, $this->qty + 1);
    }

    public function decrement(): void
    {
        $this->qty = max(1, $this->qty - 1);
    }

    public function add(): void
    {
        $this->addToCart($this->product->id, $this->qty);
        $this->qty = 1;
    }

    public function render()
    {
        $category = $this->product->categories->first();
        $related = $category
            ? $category->products()->visible()->where('products.id', '!=', $this->product->id)->inRandomOrder()->limit(4)->get()
            : collect();

        return view('livewire.product-page', compact('category', 'related'))
            ->title($this->product->name);
    }
}
