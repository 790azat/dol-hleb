<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Vercel проксирует запросы: доверяем заголовкам X-Forwarded-*, чтобы ссылки были https
        $middleware->trustProxies(at: '*');
        // Telegram присылает вебхуки без CSRF-токена (проверяем секрет в контроллере)
        $middleware->validateCsrfTokens(except: ['telegram/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        // Диагностика на проде: ?debug=SETUP_TOKEN показывает текст ошибки
        $exceptions->render(function (Throwable $e, Request $request) {
            $token = config('shop.setup_token');
            if ($token && hash_equals((string) $token, (string) $request->query('debug'))) {
                return response($e::class.': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine()."\n\n".$e->getTraceAsString(), 500)
                    ->header('Content-Type', 'text/plain; charset=utf-8');
            }
        });
        // Короткая строка об ошибке в логах Vercel (полный стек там обрезается)
        $exceptions->report(function (Throwable $e) {
            error_log('APP_ERROR '.$e::class.': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
        });
    })->create();
