<?php

namespace App\Livewire;

use App\Models\Chat;
use App\Support\Telegram;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;

/** Живой чат на сайте: сообщения уходят в Telegram администратору, ответы приходят через вебхук. */
class ChatWidget extends Component
{
    public bool $open = false;

    #[Validate('required|string|max:1000')]
    public string $text = '';

    public string $name = '';

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    private function chat(bool $create = false): ?Chat
    {
        $token = session('chat_token');
        $chat = $token ? Chat::where('token', $token)->first() : null;
        if (! $chat && $create) {
            $chat = Chat::create(['token' => (string) Str::uuid(), 'name' => $this->name ?: null]);
            session(['chat_token' => $chat->token]);
        }

        return $chat;
    }

    public function send(): void
    {
        $this->validate();
        $chat = $this->chat(create: true);
        if ($this->name && ! $chat->name) {
            $chat->update(['name' => mb_substr($this->name, 0, 60)]);
        }

        $message = $chat->messages()->create(['sender' => 'visitor', 'body' => trim($this->text)]);
        $chat->update(['last_message_at' => now(), 'unread' => true, 'page' => mb_substr((string) request()->header('Referer'), 0, 250)]);

        $tgId = Telegram::sendToAdmin("💬 {$chat->label()}\n\n{$message->body}\n\n↩️ Ответьте на это сообщение, чтобы написать посетителю.");
        if ($tgId) {
            $message->update(['telegram_message_id' => $tgId]);
        }

        $this->reset('text');
    }

    public function render()
    {
        $chat = $this->chat();

        return view('livewire.chat-widget', [
            'messages' => $chat ? $chat->messages()->latest('id')->limit(50)->get()->reverse()->values() : collect(),
            'hasName' => (bool) $chat?->name,
        ]);
    }
}
