<div class="container-x py-10">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-4xl font-bold">Админка</h1>
        <button wire:click="logout" class="btn-ghost">Выйти</button>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach (['orders' => 'Заказы'.($newOrders ? " ({$newOrders})" : ''), 'products' => 'Товары', 'reviews' => 'Отзывы'.($pendingReviews ? " ({$pendingReviews})" : '')] as $key => $label)
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
