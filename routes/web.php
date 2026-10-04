<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Middleware\AdminOnly;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::livewire('/', Livewire\Home::class)->name('home');
Route::livewire('/catalog/{slug?}', Livewire\Catalog::class)->name('catalog');
Route::livewire('/product/{slug}', Livewire\ProductPage::class)->name('product');
Route::livewire('/cart', Livewire\CartPage::class)->name('cart');
Route::livewire('/contacts', Livewire\Contacts::class)->name('contacts');
Route::livewire('/info/{slug}', Livewire\PageView::class)->name('page');

Route::livewire('/admin/login', Livewire\Admin\Login::class)->name('admin.login');
Route::livewire('/admin', Livewire\Admin\Dashboard::class)->name('admin')
    ->middleware(AdminOnly::class);

Route::get('/media/{media}.webp', MediaController::class)->whereNumber('media')->name('media');
Route::get('/setup/{token}', SetupController::class)->name('setup');
Route::post('/telegram/webhook/{secret}', TelegramWebhookController::class)->name('telegram.webhook');

// Старые адреса dol-hleb.ru → новые страницы
Route::permanentRedirect('/magazin', '/catalog');
Route::get('/magazin/folder/{slug}', fn (string $slug) => redirect()->route('catalog', $slug, 301));
Route::get('/magazin/product/{slug}', fn (string $slug) => redirect()->route('product', $slug, 301));
Route::get('/magazin/cart', fn () => redirect()->route('cart', [], 301));
Route::permanentRedirect('/kontakty', '/contacts');
foreach (['o-nas', 'otzyvy', 'dostavka', 'oplata', 'aktsii'] as $slug) {
    Route::permanentRedirect("/{$slug}", "/info/{$slug}");
}
