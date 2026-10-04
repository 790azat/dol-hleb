<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $guarded = [];

    protected $casts = ['is_visible' => 'boolean'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('position');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('is_visible', true);
    }

    public function scopeRoots(Builder $query): void
    {
        $query->whereNull('parent_id')->orderBy('position');
    }

    /** Картинка категории: своя или первая картинка любого товара. */
    public function coverUrl(): ?string
    {
        if ($this->image) {
            return asset($this->image);
        }
        $product = $this->products()->whereNotNull('images')->whereRaw("CAST(images AS TEXT) <> '[]'")->first();

        return $product?->imageUrl();
    }

    /** id категории и всех её подкатегорий. */
    public function descendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->descendantIds());
        }

        return $ids;
    }
}
