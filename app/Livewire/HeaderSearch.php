<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

/** Живой поиск в шапке: подсказки товаров и разделов по мере ввода. */
class HeaderSearch extends Component
{
    public string $q = '';

    public function go()
    {
        return $this->redirectRoute('catalog', ['q' => trim($this->q)], navigate: true);
    }

    public function render()
    {
        $term = trim($this->q);
        $products = $categories = collect();
        if (mb_strlen($term) >= 2) {
            $op = Product::query()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $products = Product::visible()->where('name', $op, "%{$term}%")->orderBy('position')->limit(6)->get();
            $categories = Category::visible()->where('name', $op, "%{$term}%")->limit(3)->get();
        }

        return view('livewire.header-search', compact('products', 'categories', 'term'));
    }
}
