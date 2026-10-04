<div class="container-x py-10">
    <nav class="text-sm text-mocha">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-berry">Главная</a>
        <span class="mx-1.5">/</span>
        <a href="{{ route('catalog') }}" wire:navigate class="hover:text-berry">Каталог</a>
        @if ($parent)
            <span class="mx-1.5">/</span><a href="{{ route('catalog', $parent->slug) }}" wire:navigate class="hover:text-berry">{{ $parent->name }}</a>
        @endif
        @if ($category)
            <span class="mx-1.5">/</span><span class="text-cocoa">{{ $category->name }}</span>
        @endif
    </nav>

    <div class="mt-4 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-4xl font-bold sm:text-5xl">{{ $category?->name ?? 'Каталог' }}</h1>
            <p class="mt-2 text-mocha">{{ trans_choice('{0} Ничего не найдено|{1} :count товар|[2,4] :count товара|[5,*] :count товаров', $products->total(), ['count' => $products->total()]) }}</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <label class="relative">
                <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-mocha" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                <input wire:model.live.debounce.350ms="search" type="search" placeholder="Поиск: торт, багет, эклер…" class="input pl-11 sm:w-72">
            </label>
            <select wire:model.live="sort" class="input sm:w-52">
                <option value="popular">По популярности</option>
                <option value="cheap">Сначала дешевле</option>
                <option value="expensive">Сначала дороже</option>
                <option value="new">Новинки</option>
            </select>
        </div>
    </div>

    <div class="-mx-4 mt-8 flex gap-2 overflow-x-auto px-4 pb-2 sm:mx-0 sm:flex-wrap sm:px-0">
        @if ($category && $subcategories->isNotEmpty())
            @if ($parent)
                <a href="{{ route('catalog', $parent->slug) }}" wire:navigate class="chip border-cocoa/15 bg-white hover:border-cocoa">← {{ $parent->name }}</a>
            @endif
            @foreach ($subcategories as $sub)
                <a href="{{ route('catalog', $sub->slug) }}" wire:navigate
                   class="chip {{ $category->id === $sub->id ? 'border-cocoa bg-cocoa text-cream' : 'border-cocoa/15 bg-white hover:border-cocoa' }}">{{ $sub->name }}</a>
            @endforeach
        @else
            <a href="{{ route('catalog') }}" wire:navigate class="chip {{ ! $category ? 'border-cocoa bg-cocoa text-cream' : 'border-cocoa/15 bg-white hover:border-cocoa' }}">Все</a>
            @foreach ($roots as $root)
                <a href="{{ route('catalog', $root->slug) }}" wire:navigate
                   class="chip {{ $category?->id === $root->id ? 'border-cocoa bg-cocoa text-cream' : 'border-cocoa/15 bg-white hover:border-cocoa' }}">{{ $root->name }}</a>
            @endforeach
        @endif
        <label class="chip cursor-pointer border-cocoa/15 bg-white">
            <input type="checkbox" wire:model.live="onlyPriced" class="mr-2 accent-berry"> С ценой
        </label>
    </div>

    @if ($category?->description)
        <div class="prose-shop mt-6 max-w-3xl">{!! $category->description !!}</div>
    @endif

    <div class="relative mt-8">
        <div wire:loading.delay.flex class="absolute inset-0 z-10 hidden items-start justify-center bg-cream/60 pt-24">
            <span class="size-10 animate-spin rounded-full border-4 border-wheat border-t-berry"></span>
        </div>
        @if ($products->isEmpty())
            <div class="rounded-3xl bg-white p-14 text-center">
                <div class="text-5xl">🧁</div>
                <p class="mt-4 text-lg font-semibold">Ничего не нашлось</p>
                <p class="mt-1 text-mocha">Попробуйте изменить запрос или загляните в другой раздел.</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
            <div class="mt-10">{{ $products->links() }}</div>
        @endif
    </div>
</div>
