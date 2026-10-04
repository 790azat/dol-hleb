<div class="container-x py-10">
    @if ($orderId)
        <div class="mx-auto max-w-xl rounded-[2rem] bg-white p-10 text-center ring-1 ring-cocoa/5">
            <div class="mx-auto grid size-16 place-items-center rounded-full bg-crust/20 text-3xl">🎉</div>
            <h1 class="mt-6 text-4xl font-bold">Спасибо за заказ!</h1>
            <p class="mt-3 text-mocha">Заказ <b class="text-cocoa">№{{ $orderId }}</b> принят. Мы перезвоним вам, чтобы подтвердить детали.</p>
            <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-8">Вернуться в каталог</a>
        </div>
    @elseif ($lines->isEmpty())
        <div class="mx-auto max-w-xl rounded-[2rem] bg-white p-10 text-center ring-1 ring-cocoa/5">
            <div class="text-6xl">🧺</div>
            <h1 class="mt-6 text-4xl font-bold">Корзина пуста</h1>
            <p class="mt-3 text-mocha">Загляните в каталог — там торты, свежий хлеб и выпечка.</p>
            <a href="{{ route('catalog') }}" wire:navigate class="btn-primary mt-8">Перейти в каталог</a>
        </div>
    @else
        <h1 class="text-4xl font-bold sm:text-5xl">Оформление заказа</h1>
        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_420px]">
            <div class="space-y-3">
                @foreach ($lines as $line)
                    @php $p = $line['product']; @endphp
                    <div wire:key="line-{{ $p->id }}" class="flex items-center gap-4 rounded-3xl bg-white p-3 pr-5 ring-1 ring-cocoa/5">
                        <a href="{{ route('product', $p->slug) }}" wire:navigate class="size-20 shrink-0 overflow-hidden rounded-2xl bg-sand sm:size-24">
                            @if ($p->imageUrl()) <img src="{{ $p->imageUrl() }}" alt="" class="size-full object-cover"> @endif
                        </a>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('product', $p->slug) }}" wire:navigate class="line-clamp-2 font-semibold hover:text-berry">{{ $p->name }}</a>
                            <div class="mt-1 text-sm text-mocha">{{ $p->formattedPrice() }}</div>
                        </div>
                        <div class="flex items-center rounded-full border border-cocoa/15">
                            <button wire:click="setQty({{ $p->id }}, {{ $line['qty'] - 1 }})" class="grid size-9 place-items-center">−</button>
                            <span class="w-7 text-center text-sm font-bold">{{ $line['qty'] }}</span>
                            <button wire:click="setQty({{ $p->id }}, {{ $line['qty'] + 1 }})" class="grid size-9 place-items-center">+</button>
                        </div>
                        <div class="hidden w-24 text-right font-bold sm:block">{{ number_format($line['sum'], 0, ',', ' ') }} ₽</div>
                        <button wire:click="remove({{ $p->id }})" class="text-mocha hover:text-berry" aria-label="Удалить">✕</button>
                    </div>
                @endforeach
            </div>

            <form wire:submit="checkout" class="h-fit space-y-4 rounded-[2rem] bg-white p-6 ring-1 ring-cocoa/5 lg:sticky lg:top-28">
                <div class="grid grid-cols-2 gap-2 rounded-full bg-sand p-1 text-sm font-semibold">
                    <label class="cursor-pointer rounded-full py-2.5 text-center transition {{ $delivery === 'pickup' ? 'bg-white shadow' : '' }}">
                        <input type="radio" wire:model.live="delivery" value="pickup" class="sr-only"> Самовывоз
                    </label>
                    <label class="cursor-pointer rounded-full py-2.5 text-center transition {{ $delivery === 'delivery' ? 'bg-white shadow' : '' }}">
                        <input type="radio" wire:model.live="delivery" value="delivery" class="sr-only"> Доставка
                    </label>
                </div>
                <div>
                    <label class="label">Имя</label>
                    <input wire:model="name" class="input" autocomplete="name">
                    @error('name') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Телефон</label>
                    <input wire:model="phone" type="tel" class="input" placeholder="+7 (___) ___-__-__" autocomplete="tel">
                    @error('phone') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
                </div>
                @if ($delivery === 'delivery')
                    <div>
                        <label class="label">Адрес доставки</label>
                        <input wire:model="address" class="input" placeholder="Долгопрудный, улица, дом, квартира">
                        @error('address') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
                    </div>
                @else
                    <p class="rounded-2xl bg-cream p-3 text-sm text-mocha">Забрать: {{ config('shop.address') }}, {{ mb_strtolower(config('shop.hours')) }}</p>
                @endif
                <div>
                    <label class="label">К какой дате</label>
                    <input wire:model="readyDate" type="date" min="{{ now()->toDateString() }}" class="input">
                    @error('readyDate') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label">Комментарий</label>
                    <textarea wire:model="comment" rows="3" class="input" placeholder="Надпись на торте, пожелания по оформлению…"></textarea>
                </div>
                <div class="flex items-center justify-between border-t border-cocoa/10 pt-4">
                    <span class="text-mocha">Итого</span>
                    <span class="font-display text-3xl font-bold">{{ number_format($total, 0, ',', ' ') }} ₽</span>
                </div>
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="checkout">Отправить заказ</span>
                    <span wire:loading wire:target="checkout">Отправляем…</span>
                </button>
                <p class="text-center text-xs text-mocha">Оплата при получении. Мы перезвоним для подтверждения.</p>
            </form>
        </div>
    @endif
</div>
