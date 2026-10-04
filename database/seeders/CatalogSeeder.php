<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Импорт каталога, собранного scripts/scrape.py со старого сайта
 * (database/data/catalog.json). Повторный запуск обновляет записи по slug.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/catalog.json');
        if (! is_file($path)) {
            $this->command?->warn('database/data/catalog.json не найден — пропускаю импорт каталога');
            $this->seedReviews();

            return;
        }

        $data = json_decode(file_get_contents($path), true);

        DB::transaction(function () use ($data) {
            $ids = [];
            foreach ($data['folders'] as $i => $f) {
                $ids[$f['slug']] = Category::updateOrCreate(['slug' => $f['slug']], [
                    'name' => $f['name'],
                    'description' => $f['description'] ?? null,
                    'position' => $f['position'] ?? $i,
                ])->id;
            }
            foreach ($data['folders'] as $f) {
                if (! empty($f['parent']) && isset($ids[$f['parent']])) {
                    Category::whereKey($ids[$f['slug']])->update(['parent_id' => $ids[$f['parent']]]);
                }
            }

            foreach ($data['products'] as $i => $p) {
                $product = Product::updateOrCreate(['slug' => $p['slug']], [
                    'name' => $p['name'] ?: $p['slug'],
                    'price' => $p['price'] ?? null,
                    'anons' => $p['anons'] ?? null,
                    'description' => $p['description'] ?? null,
                    'params' => $p['params'] ?? [],
                    'images' => $p['images_local'] ?? [],
                    'is_new' => (bool) ($p['is_new'] ?? false),
                    'position' => $i,
                ]);
                $product->categories()->sync(
                    collect($p['folders'] ?? [])->map(fn ($s) => $ids[$s] ?? null)->filter()->values()->all()
                );
            }

            // Хиты на главную: первые товары с фото из праздничных тортов и выпечки
            Product::query()->update(['is_featured' => false]);
            Product::whereNotNull('images')->where('images', '!=', '[]')->whereNotNull('price')
                ->orderBy('position')->limit(8)->update(['is_featured' => true]);

            foreach ($data['pages'] as $pg) {
                Page::updateOrCreate(['slug' => $pg['slug']], [
                    'title' => $pg['title'],
                    'body' => $pg['html'] ?? '',
                    'images' => $pg['images_local'] ?? [],
                ]);
            }
        });

        $this->seedReviews();
    }

    private function seedReviews(): void
    {
        if (Review::exists()) {
            return;
        }
        foreach ([
            ['Дмитрий', 'Красивый, вкусный, насыщенный и «неприторный» торт. Спасибо большое!'],
            ['Екатерина', 'Спасибо за тортик, он просто супер!'],
            ['Сергей', 'Спасибо огромное за вкусный и красивый тортик!'],
            ['Алёна', 'Вы волшебники! Спасибо большое ❤️'],
        ] as [$author, $text]) {
            Review::create(['author' => $author, 'text' => $text]);
        }
    }
}
