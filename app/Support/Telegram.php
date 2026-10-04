<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Бот Telegram: токен и чат администратора хранятся в настройках (админка → Настройки),
 * с запасным вариантом из переменных окружения.
 */
class Telegram
{
    public static function token(): ?string
    {
        return Setting::get('telegram_bot_token', config('shop.telegram_bot_token'));
    }

    public static function adminChatId(): ?string
    {
        return Setting::get('telegram_chat_id', config('shop.telegram_chat_id'));
    }

    public static function configured(): bool
    {
        return (bool) (self::token() && self::adminChatId());
    }

    /** Секрет вебхука: и часть URL, и заголовок X-Telegram-Bot-Api-Secret-Token. */
    public static function webhookSecret(): string
    {
        return substr(hash_hmac('sha256', 'telegram-webhook|'.self::token(), (string) config('app.key')), 0, 32);
    }

    /** Код для привязки чата администратора: /start <код>. */
    public static function linkCode(): string
    {
        return substr(hash_hmac('sha256', 'telegram-link', (string) config('app.key')), 0, 10);
    }

    public static function call(string $method, array $params = []): ?array
    {
        if (! $token = self::token()) {
            return null;
        }
        try {
            return Http::timeout(8)->asJson()->post("https://api.telegram.org/bot{$token}/{$method}", $params)->json();
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Отправить текст в чат администратора, вернуть message_id. */
    public static function sendToAdmin(string $text, ?int $replyTo = null): ?int
    {
        if (! self::configured()) {
            return null;
        }
        $params = ['chat_id' => self::adminChatId(), 'text' => mb_substr($text, 0, 4000)];
        if ($replyTo) {
            $params['reply_parameters'] = ['message_id' => $replyTo, 'allow_sending_without_reply' => true];
        }
        $res = self::call('sendMessage', $params);

        return $res['result']['message_id'] ?? null;
    }

    public static function setWebhook(string $url): array
    {
        return self::call('setWebhook', [
            'url' => $url,
            'secret_token' => self::webhookSecret(),
            'allowed_updates' => ['message'],
            'drop_pending_updates' => true,
        ]) ?? ['ok' => false, 'description' => 'Нет токена бота или Telegram недоступен'];
    }
}
