<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

/**
 * Разовая инициализация базы на Vercel (там нет консоли):
 * GET /setup/{SETUP_TOKEN} — миграции + импорт каталога из database/data/catalog.json.
 */
class SetupController extends Controller
{
    public function __invoke(string $token)
    {
        $expected = (string) config('shop.setup_token');
        abort_unless($expected !== '' && hash_equals($expected, $token), 404);

        @set_time_limit(280);
        Artisan::call('migrate', ['--force' => true]);
        $out = Artisan::output();
        Artisan::call('db:seed', ['--force' => true]);
        $out .= Artisan::output();

        return response("<pre>".e($out)."</pre>");
    }
}
