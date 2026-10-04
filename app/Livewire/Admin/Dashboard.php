<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Chat;
use App\Models\Media;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Support\Telegram;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithFileUploads, WithPagination;

    #[Url]
    public string $tab = 'overview';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $orderStatus = '';

    /** Редактируемый товар: null — форма закрыта, 0 — новый товар. */
    public ?int $editing = null;

    public array $form = [];

    public string $newCategory = '';

    /** Фото, выбранные в редакторе товара (загружаются сразу). */
    public array $photos = [];

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

    public function goChat(int $id): void
    {
        $this->tab = 'chats';
        $this->openChat($id);
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

    public function updatedOrderStatus(): void
    {
        $this->resetPage();
    }

    public function editProduct(int $id = 0): void
    {
        $p = $id ? Product::with('categories')->findOrFail($id) : new Product(['is_visible' => true]);
        $this->editing = $id;
        $this->form = [
            'name' => (string) $p->name,
            'price' => $p->price !== null ? (string) (float) $p->price : '',
            'anons' => (string) $p->anons,
            'description' => (string) $p->description,
            'images' => implode("\n", $p->images ?? []),
            'categories' => $id ? $p->categories->pluck('id')->map(fn ($v) => (string) $v)->all() : [],
            'is_visible' => (bool) $p->is_visible,
            'is_featured' => (bool) $p->is_featured,
            'is_new' => (bool) $p->is_new,
        ];
        $this->resetValidation();
    }

    public function updatedPhotos(): void
    {
        $this->validate(['photos.*' => 'image|max:10240'], [], ['photos.*' => 'фото']);
        $paths = array_filter(array_map('trim', preg_split('/\R/', (string) ($this->form['images'] ?? ''))));
        foreach ($this->photos as $file) {
            $paths[] = Media::storeUpload($file)->path();
        }
        $this->form['images'] = implode("\n", $paths);
        $this->photos = [];
    }

    public function removePhoto(int $index): void
    {
        $paths = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($this->form['images'] ?? '')))));
        unset($paths[$index]);
        $this->form['images'] = implode("\n", $paths);
    }

    public function closeProduct(): void
    {
        $this->editing = null;
    }

    public function saveProduct(): void
    {
        $this->validate([
            'form.name' => 'required|string|max:255',
            'form.price' => 'nullable|numeric|min:0',
            'form.anons' => 'nullable|string|max:500',
            'form.description' => 'nullable|string|max:20000',
            'form.images' => 'nullable|string|max:5000',
            'form.categories' => 'array',
        ], [], ['form.name' => 'название', 'form.price' => 'цена']);

        $f = $this->form;
        $data = [
            'name' => trim($f['name']),
            'price' => $f['price'] === '' ? null : (float) $f['price'],
            'anons' => $f['anons'] ?: null,
            'description' => $f['description'] ?: null,
            'images' => array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $f['images'])))),
            'is_visible' => (bool) $f['is_visible'],
            'is_featured' => (bool) $f['is_featured'],
            'is_new' => (bool) $f['is_new'],
        ];

        if ($this->editing) {
            $product = Product::findOrFail($this->editing);
            $product->update($data);
        } else {
            $slug = Str::slug($data['name']) ?: 'tovar';
            $base = $slug;
            for ($i = 2; Product::where('slug', $slug)->exists(); $i++) {
                $slug = "{$base}-{$i}";
            }
            $product = Product::create($data + ['slug' => $slug, 'position' => (int) Product::max('position') + 1]);
        }
        $product->categories()->sync(array_map('intval', $f['categories'] ?? []));

        $this->editing = null;
        $this->notice = 'Товар «'.$product->name.'» сохранён.';
    }

    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $product->categories()->detach();
        $product->delete();
        $this->editing = null;
        $this->notice = 'Товар удалён.';
    }

    public function addCategory(): void
    {
        $this->validate(['newCategory' => 'required|string|max:120'], [], ['newCategory' => 'название']);
        $slug = Str::slug($this->newCategory) ?: 'razdel';
        $base = $slug;
        for ($i = 2; Category::where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }
        Category::create([
            'name' => trim($this->newCategory), 'slug' => $slug, 'is_visible' => true,
            'position' => (int) Category::max('position') + 1,
        ]);
        $this->reset('newCategory');
    }

    public function renameCategory(int $id, string $name): void
    {
        if (trim($name) !== '') {
            Category::whereKey($id)->update(['name' => trim($name)]);
        }
    }

    public function toggleCategory(int $id): void
    {
        $c = Category::findOrFail($id);
        $c->update(['is_visible' => ! $c->is_visible]);
    }

    public function moveCategory(int $id, int $dir): void
    {
        $c = Category::findOrFail($id);
        $siblings = Category::where('parent_id', $c->parent_id)->orderBy('position')->orderBy('id')->get()->values();
        $i = $siblings->search(fn ($x) => $x->id === $c->id);
        $j = $i + $dir;
        if ($j < 0 || $j >= $siblings->count()) {
            return;
        }
        $list = $siblings->all();
        [$list[$i], $list[$j]] = [$list[$j], $list[$i]];
        foreach ($list as $pos => $cat) {
            $cat->update(['position' => $pos + 1]);
        }
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
        $like = Product::query()->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $data = match ($this->tab) {
            'products' => [
                'products' => Product::with('categories')
                    ->when($this->search, fn ($q) => $q->where('name', $like, "%{$this->search}%"))
                    ->orderBy('position')->paginate(30),
                'allCategories' => Category::orderBy('parent_id')->orderBy('position')->get(),
            ],
            'categories' => ['categories' => Category::withCount('products')->roots()->with(['children' => fn ($q) => $q->withCount('products')])->get()],
            'reviews' => ['reviews' => Review::latest()->paginate(30)],
            'chats' => [
                'chats' => Chat::whereNotNull('last_message_at')->latest('last_message_at')->paginate(30),
                'current' => $this->chatId ? Chat::with('messages')->find($this->chatId) : null,
            ],
            'settings' => ['linkCode' => Telegram::linkCode(), 'telegramReady' => Telegram::configured()],
            'orders' => ['orders' => Order::latest()
                ->when($this->orderStatus, fn ($q) => $q->where('status', $this->orderStatus))
                ->when($this->search, fn ($q) => $q->where(fn ($w) => $w->where('name', $like, "%{$this->search}%")->orWhere('phone', 'like', "%{$this->search}%")))
                ->paginate(20)],
            default => [
                'stats' => [
                    ['Новые заказы', Order::where('status', 'new')->count(), 'orders'],
                    ['Выручка за 30 дней', number_format((float) Order::where('status', '!=', 'cancelled')->where('created_at', '>=', now()->subDays(30))->sum('total'), 0, ',', ' ').' ₽', 'orders'],
                    ['Товаров на сайте', Product::visible()->count(), 'products'],
                    ['Непрочитанные чаты', Chat::where('unread', true)->count(), 'chats'],
                ],
                'recentOrders' => Order::latest()->limit(5)->get(),
                'recentChats' => Chat::whereNotNull('last_message_at')->latest('last_message_at')->limit(5)->get(),
                'telegramReady' => Telegram::configured(),
            ],
        };

        return view('livewire.admin.dashboard', $data + [
            'newOrders' => Order::where('status', 'new')->count(),
            'pendingReviews' => Review::where('is_published', false)->count(),
            'unreadChats' => Chat::where('unread', true)->count(),
        ])->title('Админка');
    }
}
