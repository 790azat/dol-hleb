<?php

namespace App\Support;

use App\Models\Order;

/** Уведомление о новом заказе в Telegram администратора (токен — в админке → Настройки). */
class OrderNotifier
{
    public static function send(Order $order): void
    {
        $items = collect($order->items)->map(fn ($i) => "• {$i['name']} × {$i['qty']} = ".number_format($i['sum'], 0, ',', ' ').' ₽')->join("\n");
        $text = "🧁 Новый заказ №{$order->id}\n{$order->name}, {$order->phone}\n"
            .($order->delivery === 'delivery' ? "Доставка: {$order->address}\n" : "Самовывоз\n")
            .($order->ready_date ? 'К дате: '.$order->ready_date->format('d.m.Y')."\n" : '')
            .($order->comment ? "Комментарий: {$order->comment}\n" : '')
            ."\n{$items}\n\nИтого: ".number_format($order->total, 0, ',', ' ').' ₽';

        Telegram::sendToAdmin($text);
    }
}
