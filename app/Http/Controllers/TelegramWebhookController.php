<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Setting;
use App\Support\Telegram;
use Illuminate\Http\Request;

/**
 * Ответы администратора из Telegram: ответ (reply) на сообщение посетителя
 * попадает в живой чат на сайте. «/start <код>» привязывает чат администратора.
 */
class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, string $secret)
    {
        $expected = Telegram::webhookSecret();
        abort_unless(hash_equals($expected, $secret), 404);
        abort_unless(hash_equals($expected, (string) $request->header('X-Telegram-Bot-Api-Secret-Token')), 403);

        $message = $request->input('message');
        if (! $message || ! isset($message['chat']['id'])) {
            return response()->json(['ok' => true]);
        }
        $chatId = (string) $message['chat']['id'];
        $text = trim((string) ($message['text'] ?? ''));

        if (str_starts_with($text, '/start')) {
            if (trim(substr($text, 6)) === Telegram::linkCode()) {
                Setting::put('telegram_chat_id', $chatId);
                Telegram::call('sendMessage', ['chat_id' => $chatId, 'text' => '✅ Готово! Сюда будут приходить заказы и сообщения из чата на сайте. Чтобы ответить посетителю — ответьте (Reply) на его сообщение.']);
            } else {
                Telegram::call('sendMessage', ['chat_id' => $chatId, 'text' => 'Это служебный бот сайта Дол-Хлеб.']);
            }

            return response()->json(['ok' => true]);
        }

        if ($chatId !== (string) Telegram::adminChatId() || $text === '') {
            return response()->json(['ok' => true]);
        }

        $replyTo = $message['reply_to_message']['message_id'] ?? null;
        $source = $replyTo ? ChatMessage::where('telegram_message_id', $replyTo)->first() : null;
        if (! $source) {
            Telegram::call('sendMessage', ['chat_id' => $chatId, 'text' => 'Чтобы ответить посетителю, нажмите «Ответить» на его сообщение.']);

            return response()->json(['ok' => true]);
        }

        $source->chat->messages()->create([
            'sender' => 'admin',
            'body' => mb_substr($text, 0, 2000),
            'telegram_message_id' => $message['message_id'] ?? null,
        ]);
        $source->chat->update(['last_message_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
