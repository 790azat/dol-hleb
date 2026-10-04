@php
    $menu = \App\Models\Category::visible()->roots()->with(['children' => fn ($q) => $q->visible()])->get();
    $mainPhone = config('shop.phones')[0];
@endphp
<header x-data="{ open: false, cat: false }" class="sticky top-0 z-40 border-b border-cocoa/5 bg-cream/85 backdrop-blur-xl">
    <div class="hidden border-b border-cocoa/5 bg-cocoa text-cream/80 md:block">
        <div class="container-x flex h-9 items-center justify-between text-xs">
            <span>{{ config('shop.address') }} · {{ config('shop.hours') }}</span>
            <div class="flex items-center gap-5">
                @foreach (config('shop.external') as $ext)
                    <a href="{{ $ext['url'] }}" target="_blank" rel="noopener" class="hover:text-white">{{ $ext['title'] }} ↗</a>
                @endforeach
                <a href="{{ config('shop.social.instagram') }}" target="_blank" rel="noopener" class="hover:text-white">Instagram</a>
            </div>
        </div>
    </div>
    <div class="container-x flex h-18 items-center gap-3 py-3 sm:gap-6">
        <x-logo />

        <nav class="ml-2 hidden items-center gap-0.5 whitespace-nowrap text-sm font-semibold lg:flex">
            <div class="relative" @mouseenter="cat = true" @mouseleave="cat = false">
                <a href="{{ route('catalog') }}" wire:navigate class="flex items-center gap-1 rounded-full px-4 py-2 hover:bg-sand">
                    Каталог
                    <svg class="size-4 transition" :class="cat && 'rotate-180'" viewBox="0 0 20 20" fill="currentColor"><path d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z"/></svg>
                </a>
                <div x-show="cat" x-cloak x-transition.opacity.duration.150ms class="absolute left-0 top-full pt-3">
                    <div class="grid w-[640px] grid-cols-2 gap-1 rounded-3xl border border-cocoa/5 bg-white p-4 shadow-2xl shadow-cocoa/10">
                        @foreach ($menu as $item)
                            <a href="{{ route('catalog', $item->slug) }}" wire:navigate class="rounded-2xl px-4 py-2.5 hover:bg-cream">
                                <span class="block">{{ $item->name }}</span>
                                @if ($item->children->isNotEmpty())
                                    <span class="block truncate text-xs font-normal text-mocha">{{ $item->children->pluck('name')->take(4)->join(', ') }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
            <a href="{{ route('catalog', 'torty-kprazdniku') }}" wire:navigate class="rounded-full px-4 py-2 hover:bg-sand">Торты на заказ</a>
            <a href="{{ route('catalog', 'nashi-raboty') }}" wire:navigate class="rounded-full px-4 py-2 hover:bg-sand">Наши работы</a>
            <a href="{{ route('page', 'dostavka') }}" wire:navigate class="rounded-full px-4 py-2 hover:bg-sand">Доставка</a>
            <a href="{{ route('contacts') }}" wire:navigate class="rounded-full px-4 py-2 hover:bg-sand">Контакты</a>
        </nav>

        <div class="ml-auto flex items-center gap-2">
            <a href="tel:{{ preg_replace('/[^\d+]/', '', $mainPhone['number']) }}" class="hidden whitespace-nowrap text-right xl:block">
                <span class="block text-sm font-bold">{{ $mainPhone['number'] }}</span>
                <span class="block text-xs text-mocha">{{ $mainPhone['label'] }}</span>
            </a>
            <livewire:cart-counter />
            <button @click="open = !open" class="grid size-11 place-items-center rounded-full bg-white lg:hidden" aria-label="Меню">
                <svg x-show="!open" class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="open" x-cloak class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </div>

    <div x-show="open" x-cloak x-transition class="max-h-[80vh] overflow-y-auto border-t border-cocoa/5 bg-cream lg:hidden">
        <div class="container-x space-y-1 py-4 text-base font-semibold">
            <a href="{{ route('catalog') }}" wire:navigate class="block rounded-2xl px-4 py-3 hover:bg-sand">Весь каталог</a>
            @foreach ($menu as $item)
                <a href="{{ route('catalog', $item->slug) }}" wire:navigate class="block rounded-2xl px-4 py-2.5 text-sm hover:bg-sand">{{ $item->name }}</a>
            @endforeach
            <hr class="my-2 border-cocoa/10">
            @foreach (['o-nas' => 'О нас', 'dostavka' => 'Доставка', 'oplata' => 'Оплата', 'aktsii' => 'Акции'] as $slug => $t)
                <a href="{{ route('page', $slug) }}" wire:navigate class="block rounded-2xl px-4 py-2.5 hover:bg-sand">{{ $t }}</a>
            @endforeach
            <a href="{{ route('contacts') }}" wire:navigate class="block rounded-2xl px-4 py-2.5 hover:bg-sand">Контакты</a>
            <div class="px-4 pt-3 text-sm font-normal text-mocha">
                @foreach (config('shop.phones') as $ph)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $ph['number']) }}" class="block py-1"><b class="text-cocoa">{{ $ph['number'] }}</b> — {{ $ph['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
</header>
