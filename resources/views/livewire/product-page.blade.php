@php $images = $product->imageUrls(); $phone = config('shop.phones')[0]; @endphp
<div class="container-x py-10">
    <nav class="text-sm text-mocha">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-berry">Главная</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('catalog') }}" wire:navigate class="hover:text-berry">Каталог</a>
        @if ($category)
            <span class="mx-1.5">/</span><a href="{{ route('catalog', $category->slug) }}" wire:navigate class="hover:text-berry">{{ $category->name }}</a>
        @endif
    </nav>

    <div class="mt-6 grid gap-10 lg:grid-cols-2 lg:gap-16">
        <div x-data="{ active: 0 }" class="space-y-4">
            <div class="relative aspect-square overflow-hidden rounded-[2rem] bg-sand">
                @forelse ($images as $i => $src)
                    <img x-show="active === {{ $i }}" @if($i) x-cloak @endif src="{{ $src }}" alt="{{ $product->name }}" class="absolute inset-0 size-full object-cover">
                @empty
                    <div class="grid size-full place-items-center text-8xl text-wheat">🎂</div>
                @endforelse
                @if ($product->is_new)
                    <span class="absolute left-5 top-5 rounded-full bg-berry px-4 py-1.5 text-sm font-bold text-white">Новинка</span>
                @endif
            </div>
            @if (count($images) > 1)
                <div class="grid grid-cols-5 gap-3">
                    @foreach ($images as $i => $src)
                        <button @click="active = {{ $i }}" class="aspect-square overflow-hidden rounded-2xl ring-2 transition" :class="active === {{ $i }} ? 'ring-berry' : 'ring-transparent opacity-70 hover:opacity-100'">
                            <img src="{{ $src }}" alt="" class="size-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:pt-4">
            <h1 class="text-4xl font-bold leading-tight sm:text-5xl">{{ $product->name }}</h1>
            @if ($product->anons)
                <p class="mt-4 text-lg text-mocha">{{ $product->anons }}</p>
            @endif

            <div class="mt-8 rounded-3xl bg-white p-6 ring-1 ring-cocoa/5">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <span class="text-sm text-mocha">Цена</span>
                        <div class="font-display text-4xl font-bold">{{ $product->formattedPrice() }}</div>
                    </div>
                    @if ($product->price)
                        <div class="flex items-center gap-3">
                            <div class="flex items-center rounded-full border border-cocoa/15">
                                <button wire:click="decrement" class="grid size-11 place-items-center text-xl">−</button>
                                <span class="w-8 text-center font-bold">{{ $qty }}</span>
                                <button wire:click="increment" class="grid size-11 place-items-center text-xl">+</button>
                            </div>
                            <button wire:click="add" wire:loading.attr="disabled" class="btn-primary">В корзину</button>
                        </div>
                    @else
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone['number']) }}" class="btn-primary">Уточнить по телефону</a>
                    @endif
                </div>
                @if ($product->params)
                    <dl class="mt-6 divide-y divide-cocoa/10 border-t border-cocoa/10">
                        @foreach ($product->params as $param)
                            <div class="flex gap-4 py-3 text-sm">
                                <dt class="w-1/3 shrink-0 text-mocha">{{ $param['name'] }}</dt>
                                <dd class="font-medium">{{ $param['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>

            <div class="mt-6 grid gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-2xl bg-sand/70 p-4"><b class="block">Заказ тортов</b><a href="tel:{{ preg_replace('/[^\d+]/', '', $phone['number']) }}" class="text-berry">{{ $phone['number'] }}</a></div>
                <div class="rounded-2xl bg-sand/70 p-4"><b class="block">Самовывоз</b>{{ config('shop.address') }}</div>
            </div>

            @if ($product->description)
                <div class="prose-shop mt-8">{!! $product->description !!}</div>
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="mt-20">
            <h2 class="text-3xl font-bold">Вам может понравиться</h2>
            <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ($related as $p)
                    <x-product-card :product="$p" />
                @endforeach
            </div>
        </section>
    @endif
</div>
