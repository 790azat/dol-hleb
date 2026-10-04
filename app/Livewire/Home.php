<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Livewire\Component;

class Home extends Component
{
    use InteractsWithCart;

    public function render()
    {
        $featured = Product::visible()->where('is_featured', true)->orderBy('position')->limit(8)->get();
        if ($featured->count() < 4) {
            $featured = Product::visible()->whereNotNull('price')->orderBy('position')->limit(8)->get();
        }

        $works = Category::where('slug', 'nashi-raboty')->first()?->products()->visible()
            ->whereNotNull('images')->latest('products.id')->limit(10)->get() ?? collect();

        return view('livewire.home', [
            'categories' => Category::visible()->roots()->get(),
            'featured' => $featured,
            'works' => $works,
            'reviews' => Review::where('is_published', true)->latest()->limit(6)->get(),
        ]);
    }
}
