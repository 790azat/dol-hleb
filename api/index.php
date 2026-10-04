<?php

// Точка входа для Vercel (runtime vercel-php): отдаём запрос Laravel.

// Пока к проекту не подключена база Postgres (DATABASE_URL), сайт работает
// на готовой SQLite-копии каталога. Она копируется в /tmp при холодном старте,
// поэтому заказы и чаты в этом режиме временные (уведомления в Telegram доходят).
if (! getenv('DATABASE_URL')) {
    $db = '/tmp/database.sqlite';
    if (! file_exists($db)) {
        copy(__DIR__.'/../database/seed.sqlite', $db);
    }
    foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $db] as $key => $value) {
        putenv("$key=$value");
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
}

require __DIR__.'/../public/index.php';
