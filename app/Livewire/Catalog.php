<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Catalog extends Component
{
    use InteractsWithCart, WithPagination;

    public ?Category $category = null;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: 'popular')]
    public string $sort = 'popular';

    #[Url(as: 'price', except: false)]
    public bool $onlyPriced = false;

    public function mount(?string $slug = null): void
    {
        if ($slug) {
            $this->category = Category::visible()->where('slug', $slug)->firstOrFail();
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'sort', 'onlyPriced'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = Product::visible()->with('categories');

        if ($this->category) {
            $ids = $this->category->descendantIds();
            $query->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids));
        }

        if ($term = trim($this->search)) {
            // ILIKE в Postgres корректно ищет по кириллице без учёта регистра
            $op = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(fn ($q) => $q->where('name', $op, "%{$term}%")->orWhere('anons', $op, "%{$term}%"));
        }

        if ($this->onlyPriced) {
            $query->whereNotNull('price');
        }

        match ($this->sort) {
            'cheap' => $query->orderByRaw('price IS NULL')->orderBy('price'),
            'expensive' => $query->orderByRaw('price IS NULL')->orderByDesc('price'),
            'new' => $query->orderByDesc('is_new')->orderByDesc('id'),
            default => $query->orderBy('position'),
        };

        $parent = $this->category?->parent;

        return view('livewire.catalog', [
            'products' => $query->paginate(24),
            'roots' => Category::visible()->roots()->get(),
            'subcategories' => $this->category
                ? ($this->category->children()->visible()->get()->whenEmpty(fn () => $parent?->children()->visible()->get() ?? collect()))
                : collect(),
            'parent' => $parent,
        ])->title($this->category?->name ?? 'Каталог');
    }
}
