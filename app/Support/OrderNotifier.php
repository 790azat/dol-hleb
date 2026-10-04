<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Throwable;

/** Уведомление о новом заказе в Telegram (если заданы TELEGRAM_BOT_TOKEN и TELEGRAM_CHAT_ID). */
class OrderNotifier
{
    public static function send(Order $order): void
    {
        $token = config('shop.telegram_bot_token');
        $chat = config('shop.telegram_chat_id');
        if (! $token || ! $chat) {
            return;
        }

        $items = collect($order->items)->map(fn ($i) => "• {$i['name']} × {$i['qty']} = ".number_format($i['sum'], 0, ',', ' ').' ₽')->join("\n");
        $text = "🧁 Новый заказ №{$order->id}\n{$order->name}, {$order->phone}\n"
            .($order->delivery === 'delivery' ? "Доставка: {$order->address}\n" : "Самовывоз\n")
            .($order->ready_date ? 'К дате: '.$order->ready_date->format('d.m.Y')."\n" : '')
            .($order->comment ? "Комментарий: {$order->comment}\n" : '')
            ."\n{$items}\n\nИтого: ".number_format($order->total, 0, ',', ' ').' ₽';

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", ['chat_id' => $chat, 'text' => $text]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
