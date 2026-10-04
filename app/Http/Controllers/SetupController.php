<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Разовая инициализация базы на Vercel (там нет консоли):
 * GET /setup/{SETUP_TOKEN} — миграции; импорт каталога из database/data/catalog.json
 * только пока база пуста или с ?seed=1 (иначе затёрлись бы правки из админки).
 */
class SetupController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $expected = (string) config('shop.setup_token');
        abort_unless($expected !== '' && hash_equals($expected, $token), 404);

        @set_time_limit(280);
        Artisan::call('migrate', ['--force' => true]);
        $out = Artisan::output();
        if ($request->boolean('seed') || ! Product::exists()) {
            Artisan::call('db:seed', ['--force' => true]);
            $out .= Artisan::output();
        }

        return response("<pre>".e($out)."</pre>");
    }
}
