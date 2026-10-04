<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'params' => 'array',
        'images' => 'array',
        'price' => 'float',
        'is_new' => 'boolean',
        'is_featured' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    public function imageUrl(int $index = 0): ?string
    {
        $path = $this->images[$index] ?? null;

        return $path ? self::url($path) : null;
    }

    public function imageUrls(): array
    {
        return array_map(fn ($p) => self::url($p), $this->images ?? []);
    }

    /** Фото из репозитория (public/images) или внешняя ссылка, добавленная в админке. */
    public static function url(string $path): string
    {
        return match (true) {
            (bool) preg_match('~^https?://~', $path) => $path,
            str_starts_with($path, '/') => url($path),
            default => asset('images/'.$path),
        };
    }

    public function formattedPrice(): string
    {
        return $this->price ? number_format($this->price, 0, ',', ' ').' ₽' : 'Цена по запросу';
    }
}
