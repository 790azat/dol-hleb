# Дол-Хлеб — новый сайт

Новый сайт пекарни и кондитерской «Дол-Хлеб» (Долгопрудный) вместо старого https://dol-hleb.ru.

**Стек:** Laravel 13, Livewire 4, Tailwind CSS 4, PostgreSQL (Neon через Vercel), хостинг на Vercel (`vercel-php`).

## Что есть
- Главная, каталог с разделами, поиском и сортировкой, карточка товара с галереей
- Корзина и оформление заказа (самовывоз/доставка, дата), заказы сохраняются в БД
- Страницы «О нас», «Доставка», «Оплата», «Акции», «Отзывы» (с формой отзыва), «Контакты» с картой
- Админка `/admin` (пароль из `ADMIN_PASSWORD`): заказы и их статусы, цены и видимость товаров, модерация отзывов
- Редиректы со старых адресов (`/magazin/folder/...`, `/magazin/product/...`, `/o-nas` и т.д.)
- Уведомления о заказах в Telegram (необязательно: `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`)

## Контент со старого сайта
`scripts/scrape.py` скачивает разделы, товары, тексты и фото со старого сайта в
`database/data/catalog.json` и `public/images/` (фото сжимаются в WebP и хранятся в репозитории,
чтобы не тратить лимиты хранилища Vercel). Запускается GitHub Action **«Импорт со старого сайта»**
(вкладка Actions → Run workflow) и сам коммитит результат.

## Деплой на Vercel
1. Проект Vercel подключён к этому репозиторию; база — Neon Postgres (переменная `DATABASE_URL`).
2. Переменные окружения: `APP_KEY`, `APP_URL`, `ADMIN_PASSWORD`, `SETUP_TOKEN`.
3. После деплоя один раз открыть `https://<домен>/setup/<SETUP_TOKEN>` — создаст таблицы и
   импортирует каталог из `catalog.json`. Повторный вызов обновляет каталог (заказы не трогает).

Стили собираются локально (`npm run build`) и коммитятся в `public/build`.

## Локально
```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate --seed
npm run build && php artisan serve
```
