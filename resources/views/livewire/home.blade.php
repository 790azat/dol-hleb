<div>
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute -right-40 -top-40 size-[520px] rounded-full bg-wheat/50 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-20 size-[420px] rounded-full bg-berry/10 blur-3xl"></div>
        <div class="container-x relative grid items-center gap-12 py-14 lg:grid-cols-2 lg:py-24">
            <div>
                <p class="eyebrow">Пекарня · Кондитерская · Долгопрудный</p>
                <h1 class="mt-5 text-5xl font-bold leading-[1.05] sm:text-6xl lg:text-7xl">
                    Торты на заказ <span class="italic text-berry">и свежий</span> хлеб каждый день
                </h1>
                <p class="mt-6 max-w-xl text-lg text-mocha">
                    Свадебные, детские, тематические и фото-торты любой сложности. Ароматные булочки, выпечка и десерты — готовим вручную, без ГМО и некачественного сырья.
                </p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="{{ route('catalog', 'torty-kprazdniku') }}" wire:navigate class="btn-primary">Выбрать торт</a>
                    <a href="{{ route('catalog') }}" wire:navigate class="btn-ghost">Весь каталог</a>
                </div>
                <dl class="mt-12 grid max-w-lg grid-cols-3 gap-6 border-t border-cocoa/10 pt-8">
                    <div><dt class="text-xs text-mocha">Работаем</dt><dd class="mt-1 font-display text-2xl font-bold">8:00–20:00</dd></div>
                    <div><dt class="text-xs text-mocha">Торт на заказ</dt><dd class="mt-1 font-display text-2xl font-bold">от 1 дня</dd></div>
                    <div><dt class="text-xs text-mocha">Ассортимент</dt><dd class="mt-1 font-display text-2xl font-bold">{{ \App\Models\Product::visible()->count() ?: '500' }}+</dd></div>
                </dl>
            </div>
            <div class="relative">
                @php $heroImages = $works->take(3)->map->imageUrl()->filter()->values(); if ($heroImages->count() < 3) { $heroImages = $featured->take(3)->map->imageUrl()->filter()->values(); } @endphp
                <div class="grid grid-cols-5 grid-rows-6 gap-4 h-[460px] sm:h-[560px]">
                    <div class="col-span-3 row-span-6 overflow-hidden rounded-[2.5rem] bg-sand shadow-2xl shadow-cocoa/20">
                        @if ($heroImages->get(0)) <img src="{{ $heroImages->get(0) }}" alt="" class="size-full object-cover"> @endif
                    </div>
                    <div class="col-span-2 row-span-3 overflow-hidden rounded-[2rem] bg-wheat">
                        @if ($heroImages->get(1)) <img src="{{ $heroImages->get(1) }}" alt="" class="size-full object-cover"> @endif
                    </div>
                    <div class="col-span-2 row-span-3 overflow-hidden rounded-[2rem] bg-berry/20">
                        @if ($heroImages->get(2)) <img src="{{ $heroImages->get(2) }}" alt="" class="size-full object-cover"> @endif
                    </div>
                </div>
                <div class="absolute -bottom-5 left-6 flex items-center gap-3 rounded-2xl bg-white px-5 py-4 shadow-xl">
                    <span class="text-2xl">★</span>
                    <div><b class="block text-sm">«Вы волшебники!»</b><span class="text-xs text-mocha">— отзыв покупателя</span></div>
                </div>
            </div>
        </div>
    </section>

    {{-- Категории --}}
    <section class="container-x py-16">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="eyebrow">Каталог</p>
                <h2 class="mt-2 text-4xl font-bold">Что испечь для вас?</h2>
            </div>
            <a href="{{ route('catalog') }}" wire:navigate class="hidden text-sm font-semibold text-berry hover:underline sm:block">Смотреть всё →</a>
        </div>
        <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories->take(12) as $i => $category)
                <a href="{{ route('catalog', $category->slug) }}" wire:navigate
                   class="group relative flex aspect-[4/3] items-end overflow-hidden rounded-3xl bg-sand p-5 {{ $i === 0 ? 'sm:col-span-2 sm:row-span-2 sm:aspect-auto' : '' }}">
                    @if ($cover = $category->coverUrl())
                        <img src="{{ $cover }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-105">
                        <span class="absolute inset-0 bg-gradient-to-t from-cocoa/80 via-cocoa/10 to-transparent"></span>
                    @endif
                    <span class="relative font-display text-xl font-bold {{ $cover ? 'text-white' : '' }} {{ $i === 0 ? 'sm:text-3xl' : '' }}">{{ $category->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Хиты --}}
    @if ($featured->isNotEmpty())
        <section class="bg-sand/60 py-16">
            <div class="container-x">
                <p class="eyebrow">Популярное</p>
                <h2 class="mt-2 text-4xl font-bold">Любимое у покупателей</h2>
                <div class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($featured as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Преимущества --}}
    <section class="container-x grid gap-6 py-20 md:grid-cols-3">
        @foreach ([
            ['🌾', 'Натуральные ингредиенты', 'Без ГМО, пищевых добавок и некачественного сырья. Только проверенные поставщики.'],
            ['👩‍🍳', 'Ручная работа', 'Современное оборудование и контроль качества, но многое делаем руками — чтобы сохранить традиционный вкус.'],
            ['🎂', 'Торт любой сложности', 'Свадебные, детские, тематические и фото-торты. Обсудим дизайн, начинку и вес.'],
        ] as [$icon, $t, $d])
            <div class="rounded-3xl border border-cocoa/10 bg-white p-8">
                <span class="text-4xl">{{ $icon }}</span>
                <h3 class="mt-5 text-2xl font-bold">{{ $t }}</h3>
                <p class="mt-3 text-mocha">{{ $d }}</p>
            </div>
        @endforeach
    </section>

    {{-- Наши работы --}}
    @if ($works->count() >= 4)
        <section class="py-6">
            <div class="container-x flex items-end justify-between gap-4">
                <div>
                    <p class="eyebrow">Портфолио</p>
                    <h2 class="mt-2 text-4xl font-bold">Наши работы</h2>
                </div>
                <a href="{{ route('catalog', 'nashi-raboty') }}" wire:navigate class="text-sm font-semibold text-berry hover:underline">Все работы →</a>
            </div>
            <div class="mt-8 flex snap-x gap-4 overflow-x-auto px-4 pb-4 sm:px-6 lg:px-[max(2rem,calc((100vw-80rem)/2+2rem))]">
                @foreach ($works as $work)
                    <a href="{{ route('product', $work->slug) }}" wire:navigate class="group relative w-64 shrink-0 snap-start overflow-hidden rounded-3xl sm:w-72">
                        <img src="{{ $work->imageUrl() }}" alt="{{ $work->name }}" loading="lazy" class="aspect-[3/4] w-full object-cover transition duration-500 group-hover:scale-105">
                        <span class="absolute inset-x-3 bottom-3 rounded-2xl bg-white/90 px-4 py-2 text-sm font-semibold backdrop-blur">{{ $work->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Отзывы --}}
    @if ($reviews->isNotEmpty())
        <section class="container-x py-16">
            <p class="eyebrow">Отзывы</p>
            <h2 class="mt-2 text-4xl font-bold">Нам пишут</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($reviews as $review)
                    <figure class="flex flex-col rounded-3xl bg-white p-6 ring-1 ring-cocoa/5">
                        <div class="text-crust">{{ str_repeat('★', $review->rating) }}</div>
                        <blockquote class="mt-3 flex-1 text-cocoa">«{{ $review->text }}»</blockquote>
                        <figcaption class="mt-5 text-sm font-semibold text-mocha">{{ $review->author }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="container-x">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-cocoa px-8 py-14 text-cream sm:px-14">
            <div class="absolute -right-24 -top-24 size-80 rounded-full bg-crust/30 blur-3xl"></div>
            <div class="relative grid items-center gap-8 lg:grid-cols-2">
                <div>
                    <h2 class="text-4xl font-bold sm:text-5xl">Закажите торт мечты</h2>
                    <p class="mt-4 max-w-lg text-cream/70">Позвоните или выберите торт в каталоге — уточним начинку, вес и оформление и приготовим к нужной дате.</p>
                </div>
                <div class="flex flex-wrap gap-3 lg:justify-end">
                    @php $ph = config('shop.phones')[0]; @endphp
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $ph['number']) }}" class="btn bg-cream text-cocoa hover:bg-white">{{ $ph['number'] }}</a>
                    <a href="{{ route('catalog', 'torty-kprazdniku') }}" wire:navigate class="btn-primary">Каталог тортов</a>
                </div>
            </div>
        </div>
    </section>
</div>
