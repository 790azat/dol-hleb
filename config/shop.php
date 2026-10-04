<?php

return [
    'name' => 'Дол-Хлеб',
    'tagline' => 'Пекарня и кондитерская в Долгопрудном',
    'city' => 'Долгопрудный',
    'address' => 'г. Долгопрудный, ул. Спортивная, 10',
    'hours' => 'Ежедневно с 8:00 до 20:00',
    'map_query' => 'Долгопрудный, Спортивная улица, 10',
    'phones' => [
        ['number' => '+7 (963) 629-00-08', 'label' => 'Заказ тортов'],
        ['number' => '+7 (965) 158-99-00', 'label' => 'Шашлык и пицца'],
        ['number' => '+7 (968) 738-88-08', 'label' => 'Шашлык и пицца, Школьная'],
    ],
    'social' => [
        'instagram' => 'https://www.instagram.com/dol_hleb/',
        'facebook' => 'https://www.facebook.com/tiktak.taktak',
    ],
    // Внешние разделы старого меню
    'external' => [
        ['title' => 'Пицца', 'url' => 'https://dol-food.ru/'],
        ['title' => 'Шашлык', 'url' => 'https://dol-food.ru/'],
    ],
    // Пароль для /admin (заказы и товары)
    'admin_password' => env('ADMIN_PASSWORD'),
    // Токен для /setup/{token}: миграции и импорт каталога на Vercel
    'setup_token' => env('SETUP_TOKEN'),
    // Куда слать уведомления о заказах (необязательно)
    'telegram_bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'telegram_chat_id' => env('TELEGRAM_CHAT_ID'),
];
