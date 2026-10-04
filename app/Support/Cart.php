<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/** Корзина в сессии: [product_id => qty]. */
class Cart
{
    private const KEY = 'cart';

    public static function raw(): array
    {
        return session(self::KEY, []);
    }

    public static function add(int $productId, int $qty = 1): void
    {
        $cart = self::raw();
        $cart[$productId] = min(99, ($cart[$productId] ?? 0) + max(1, $qty));
        session([self::KEY => $cart]);
    }

    public static function set(int $productId, int $qty): void
    {
        $cart = self::raw();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = min(99, $qty);
        }
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    public static function count(): int
    {
        return array_sum(self::raw());
    }

    /** @return Collection<int, array{product: Product, qty: int, sum: float}> */
    public static function lines(): Collection
    {
        $raw = self::raw();
        if (! $raw) {
            return collect();
        }

        return Product::whereIn('id', array_keys($raw))->get()
            ->map(fn (Product $p) => [
                'product' => $p,
                'qty' => $raw[$p->id],
                'sum' => ($p->price ?? 0) * $raw[$p->id],
            ])->values();
    }

    public static function total(): float
    {
        return self::lines()->sum('sum');
    }
}
