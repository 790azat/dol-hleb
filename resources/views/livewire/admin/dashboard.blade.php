<div class="container-x py-10">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-4xl font-bold">Админка</h1>
        <button wire:click="logout" class="btn-ghost">Выйти</button>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach (['orders' => 'Заказы'.($newOrders ? " ({$newOrders})" : ''), 'chats' => 'Чаты'.($unreadChats ? " ({$unreadChats})" : ''), 'products' => 'Товары', 'reviews' => 'Отзывы'.($pendingReviews ? " ({$pendingReviews})" : ''), 'settings' => 'Настройки'] as $key => $label)
            <button wire:click="$set('tab', '{{ $key }}')" class="chip {{ $tab === $key ? 'border-cocoa bg-cocoa text-cream' : 'border-cocoa/15 bg-white' }}">{{ $label }}</button>
        @endforeach
    </div>

    @if ($tab === 'orders')
        <div class="mt-6 space-y-3">
            @forelse ($orders as $order)
                <div wire:key="o-{{ $order->id }}" class="rounded-3xl bg-white p-5 ring-1 ring-cocoa/5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <b class="text-lg">№{{ $order->id }} · {{ $order->name }}</b>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $order->phone) }}" class="ml-2 text-berry">{{ $order->phone }}</a>
                            <div class="text-sm text-mocha">
                                {{ $order->created_at->format('d.m.Y H:i') }} ·
                                {{ $order->delivery === 'delivery' ? 'Доставка: '.$order->address : 'Самовывоз' }}
                                @if ($order->ready_date) · к {{ $order->ready_date->format('d.m.Y') }} @endif
                            </div>
                        </div>
                        <select wire:change="setStatus({{ $order->id }}, $event.target.value)" class="input w-44 py-2">
                            @foreach (\App\Models\Order::STATUSES as $k => $v)
                                <option value="{{ $k }}" @selected($order->status === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <ul class="mt-3 text-sm">
                        @foreach ($order->items as $item)
                            <li>{{ $item['name'] }} × {{ $item['qty'] }} — {{ number_format($item['sum'], 0, ',', ' ') }} ₽</li>
                        @endforeach
                    </ul>
                    @if ($order->comment) <p class="mt-2 rounded-2xl bg-cream p-3 text-sm">{{ $order->comment }}</p> @endif
                    <p class="mt-2 font-bold">Итого: {{ number_format($order->total, 0, ',', ' ') }} ₽</p>
                </div>
            @empty
                <p class="text-mocha">Заказов пока нет.</p>
            @endforelse
            {{ $orders->links() }}
        </div>
    @elseif ($tab === 'products')
        <input wire:model.live.debounce.300ms="search" class="input mt-6 max-w-sm" placeholder="Поиск товара">
        <div class="mt-4 overflow-x-auto rounded-3xl bg-white ring-1 ring-cocoa/5">
            <table class="w-full text-sm">
                <thead class="text-left text-mocha"><tr><th class="p-3">Товар</th><th class="p-3">Цена, ₽</th><th class="p-3">Показ</th><th class="p-3">Хит</th><th class="p-3">Новинка</th></tr></thead>
                <tbody class="divide-y divide-cocoa/5">
                    @foreach ($products as $p)
                        <tr wire:key="pr-{{ $p->id }}">
                            <td class="p-3"><div class="flex items-center gap-3">
                                @if ($p->imageUrl()) <img src="{{ $p->imageUrl() }}" class="size-10 rounded-xl object-cover" alt=""> @endif
                                <a href="{{ route('product', $p->slug) }}" target="_blank" class="hover:text-berry">{{ $p->name }}</a></div></td>
                            <td class="p-3"><input value="{{ $p->price }}" wire:change="savePrice({{ $p->id }}, $event.target.value)" class="input w-28 py-1.5"></td>
                            @foreach (['is_visible', 'is_featured', 'is_new'] as $f)
                                <td class="p-3"><input type="checkbox" @checked($p->{$f}) wire:click="toggle({{ $p->id }}, '{{ $f }}')" class="size-5 accent-berry"></td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $products->links() }}</div>
    @elseif ($tab === 'chats')
        <div class="mt-6 grid gap-4 lg:grid-cols-[320px_1fr]" wire:poll.5s>
            <div class="space-y-2">
                @forelse ($chats as $c)
                    <button wire:key="c-{{ $c->id }}" wire:click="openChat({{ $c->id }})" class="block w-full rounded-2xl p-4 text-left ring-1 transition {{ $chatId === $c->id ? 'bg-cocoa text-cream ring-cocoa' : 'bg-white ring-cocoa/5 hover:ring-crust' }}">
                        <span class="flex items-center justify-between gap-2"><b>{{ $c->label() }}</b>@if ($c->unread)<span class="size-2.5 rounded-full bg-berry"></span>@endif</span>
                        <span class="text-xs opacity-60">{{ $c->last_message_at?->format('d.m H:i') }}</span>
                    </button>
                @empty
                    <p class="text-mocha">Сообщений пока нет.</p>
                @endforelse
                {{ $chats->links() }}
            </div>
            <div class="flex min-h-[420px] flex-col rounded-3xl bg-white ring-1 ring-cocoa/5">
                @if ($current)
                    <div class="border-b border-cocoa/5 p-4"><b>{{ $current->label() }}</b> @if ($current->page)<span class="text-xs text-mocha">· {{ $current->page }}</span>@endif</div>
                    <div class="flex-1 space-y-2 overflow-y-auto bg-cream p-4">
                        @foreach ($current->messages as $m)
                            <div class="flex {{ $m->sender === 'admin' ? 'justify-end' : '' }}">
                                <div class="max-w-[80%] whitespace-pre-line rounded-2xl px-4 py-2 text-sm {{ $m->sender === 'admin' ? 'bg-cocoa text-cream' : 'bg-white' }}">{{ $m->body }}<span class="block text-[10px] opacity-50">{{ $m->created_at->format('d.m H:i') }}</span></div>
                            </div>
                        @endforeach
                    </div>
                    <form wire:submit="sendReply" class="flex gap-2 border-t border-cocoa/5 p-3">
                        <input wire:model="reply" class="input" placeholder="Ответ посетителю…">
                        <button class="btn-primary">Отправить</button>
                    </form>
                @else
                    <p class="m-auto text-mocha">Выберите чат слева. Отвечать можно и прямо из Telegram.</p>
                @endif
            </div>
        </div>
    @elseif ($tab === 'settings')
        <form wire:submit="saveSettings" class="mt-6 max-w-2xl space-y-5 rounded-3xl bg-white p-6 ring-1 ring-cocoa/5">
            <h2 class="text-2xl font-bold">Telegram: живой чат и заказы</h2>
            <ol class="list-decimal space-y-1 pl-5 text-sm text-mocha">
                <li>Создайте бота у <a href="https://t.me/BotFather" target="_blank" class="text-berry">@BotFather</a> командой /newbot и вставьте токен ниже.</li>
                <li>Нажмите «Сохранить» — сайт подключит вебхук.</li>
                <li>Напишите своему боту: <code class="rounded bg-sand px-1.5 py-0.5">/start {{ $linkCode }}</code> — чат привяжется автоматически.</li>
                <li>Сообщения из чата и новые заказы будут приходить в Telegram. Ответьте (Reply) на сообщение — посетитель увидит ответ на сайте.</li>
            </ol>
            <div>
                <label class="label">Токен бота</label>
                <input wire:model="botToken" class="input font-mono" placeholder="123456789:AA…">
                @error('botToken') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="label">ID чата администратора</label>
                <input wire:model="adminChat" class="input font-mono" placeholder="заполнится после /start {{ $linkCode }}">
                @error('adminChat') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
            </div>
            @if ($notice) <p class="rounded-2xl bg-crust/15 p-3 text-sm">{{ $notice }}</p> @endif
            <div class="flex flex-wrap items-center gap-3">
                <button class="btn-dark">Сохранить</button>
                <button type="button" wire:click="testTelegram" class="btn-ghost">Отправить тест</button>
                <span class="text-sm {{ $telegramReady ? 'text-green-700' : 'text-mocha' }}">{{ $telegramReady ? '● Подключено' : '○ Не подключено' }}</span>
            </div>
        </form>
    @else
        <div class="mt-6 space-y-3">
            @forelse ($reviews as $r)
                <div wire:key="r-{{ $r->id }}" class="flex flex-wrap items-start justify-between gap-3 rounded-3xl bg-white p-5 ring-1 ring-cocoa/5">
                    <div><b>{{ $r->author }}</b> <span class="text-xs text-mocha">{{ $r->created_at->format('d.m.Y') }}</span><p class="mt-1">{{ $r->text }}</p></div>
                    <div class="flex gap-2">
                        <button wire:click="publishReview({{ $r->id }}, {{ $r->is_published ? 'false' : 'true' }})" class="chip {{ $r->is_published ? 'border-cocoa/15' : 'border-berry bg-berry text-white' }}">{{ $r->is_published ? 'Скрыть' : 'Опубликовать' }}</button>
                        <button wire:click="deleteReview({{ $r->id }})" wire:confirm="Удалить отзыв?" class="chip border-cocoa/15">Удалить</button>
                    </div>
                </div>
            @empty
                <p class="text-mocha">Отзывов нет.</p>
            @endforelse
            {{ $reviews->links() }}
        </div>
    @endif
</div>
