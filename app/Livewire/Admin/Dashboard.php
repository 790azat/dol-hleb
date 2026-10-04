<?php

namespace App\Livewire\Admin;

use App\Models\Chat;
use App\Models\Order;
use App\Models\Setting;
use App\Support\Telegram;
use App\Models\Product;
use App\Models\Review;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'orders';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public ?int $chatId = null;

    public string $reply = '';

    public string $botToken = '';

    public string $adminChat = '';

    public ?string $notice = null;

    public function mount(): void
    {
        abort_unless(session('admin'), 403);
        $this->botToken = (string) Setting::get('telegram_bot_token', '');
        $this->adminChat = (string) Setting::get('telegram_chat_id', '');
    }

    public function openChat(int $id): void
    {
        $this->chatId = $id;
        Chat::whereKey($id)->update(['unread' => false]);
    }

    public function sendReply(): void
    {
        $this->validate(['reply' => 'required|string|max:2000']);
        $chat = Chat::findOrFail($this->chatId);
        $chat->messages()->create(['sender' => 'admin', 'body' => trim($this->reply)]);
        $chat->update(['last_message_at' => now(), 'unread' => false]);
        $this->reset('reply');
    }

    public function saveSettings(): void
    {
        $this->validate([
            'botToken' => ['nullable', 'string', 'max:100', 'regex:/^\d+:[\w-]+$/'],
            'adminChat' => ['nullable', 'string', 'max:40'],
        ], ['botToken.regex' => 'Токен выглядит как 123456789:ABC-def…']);

        Setting::put('telegram_bot_token', trim($this->botToken));
        Setting::put('telegram_chat_id', trim($this->adminChat));

        if (trim($this->botToken) === '') {
            $this->notice = 'Сохранено. Telegram отключён.';

            return;
        }

        $res = Telegram::setWebhook(route('telegram.webhook', Telegram::webhookSecret()));
        $me = Telegram::call('getMe');
        $this->notice = ($res['ok'] ?? false)
            ? 'Сохранено, вебхук подключён к боту @'.($me['result']['username'] ?? '?').'. '
                .($this->adminChat ? '' : 'Теперь отправьте боту: /start '.Telegram::linkCode())
            : 'Сохранено, но Telegram ответил: '.($res['description'] ?? 'ошибка');
    }

    public function testTelegram(): void
    {
        $this->adminChat = (string) Setting::get('telegram_chat_id', '');
        $this->notice = Telegram::sendToAdmin('🔔 Проверка: сайт Дол-Хлеб подключён.')
            ? 'Тестовое сообщение отправлено в Telegram.'
            : 'Не получилось: проверьте токен и что чат привязан (/start '.Telegram::linkCode().').';
    }

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setStatus(int $orderId, string $status): void
    {
        abort_unless(array_key_exists($status, Order::STATUSES), 422);
        Order::whereKey($orderId)->update(['status' => $status]);
    }

    public function savePrice(int $productId, $price): void
    {
        $price = $price === '' || $price === null ? null : max(0, (float) str_replace([' ', ','], ['', '.'], (string) $price));
        Product::whereKey($productId)->update(['price' => $price]);
    }

    public function toggle(int $productId, string $field): void
    {
        abort_unless(in_array($field, ['is_visible', 'is_featured', 'is_new']), 422);
        $p = Product::findOrFail($productId);
        $p->update([$field => ! $p->{$field}]);
    }

    public function publishReview(int $id, bool $publish): void
    {
        Review::whereKey($id)->update(['is_published' => $publish]);
    }

    public function deleteReview(int $id): void
    {
        Review::whereKey($id)->delete();
    }

    public function logout()
    {
        session()->forget('admin');

        return $this->redirectRoute('home');
    }

    public function render()
    {
        $data = match ($this->tab) {
            'products' => ['products' => Product::when($this->search, fn ($q) => $q->where('name', $q->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like', "%{$this->search}%"))->orderBy('position')->paginate(30)],
            'reviews' => ['reviews' => Review::latest()->paginate(30)],
            'chats' => [
                'chats' => Chat::whereNotNull('last_message_at')->latest('last_message_at')->paginate(30),
                'current' => $this->chatId ? Chat::with('messages')->find($this->chatId) : null,
            ],
            'settings' => ['linkCode' => Telegram::linkCode(), 'telegramReady' => Telegram::configured()],
            default => ['orders' => Order::latest()->paginate(20)],
        };

        return view('livewire.admin.dashboard', $data + [
            'newOrders' => Order::where('status', 'new')->count(),
            'pendingReviews' => Review::where('is_published', false)->count(),
            'unreadChats' => Chat::where('unread', true)->count(),
        ])->title('Админка');
    }
}
