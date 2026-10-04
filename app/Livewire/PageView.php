<?php

namespace App\Livewire;

use App\Models\Page;
use App\Models\Review;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PageView extends Component
{
    public Page $page;

    #[Validate('required|string|max:60', as: 'имя')]
    public string $author = '';

    #[Validate('required|string|min:5|max:1000', as: 'отзыв')]
    public string $text = '';

    public bool $sent = false;

    public function mount(string $slug): void
    {
        $this->page = Page::where('slug', $slug)->first()
            ?? new Page(['slug' => $slug, 'title' => self::fallbackTitle($slug), 'body' => '']);
        abort_if(! $this->page->exists && ! self::fallbackTitle($slug), 404);
    }

    public static function fallbackTitle(string $slug): ?string
    {
        return [
            'o-nas' => 'О нас', 'otzyvy' => 'Отзывы', 'dostavka' => 'Доставка',
            'oplata' => 'Оплата', 'aktsii' => 'Акции',
        ][$slug] ?? null;
    }

    public function sendReview(): void
    {
        $this->validate();
        Review::create(['author' => $this->author, 'text' => $this->text, 'is_published' => false]);
        $this->reset('author', 'text');
        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.page-view', [
            'reviews' => $this->page->slug === 'otzyvy' ? Review::where('is_published', true)->latest()->get() : collect(),
        ])->title($this->page->title);
    }
}
