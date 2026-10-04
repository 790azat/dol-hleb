<form wire:submit="go" x-data="{ focus: false }" @click.outside="focus = false" class="relative w-full">
    <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-mocha" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
    <input wire:model.live.debounce.250ms="q" @focus="focus = true" @keydown.escape="focus = false" type="search"
           placeholder="Найти торт, хлеб, эклер…" class="h-11 w-full rounded-full border border-cocoa/10 bg-white pl-11 pr-4 text-sm outline-none transition focus:border-crust focus:ring-4 focus:ring-crust/15">
    @if (mb_strlen($term) >= 2)
        <div x-show="focus" x-cloak class="absolute right-0 top-full z-50 mt-2 w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-3xl bg-white shadow-2xl shadow-cocoa/15 ring-1 ring-cocoa/5">
            @forelse ($categories as $c)
                <a href="{{ route('catalog', $c->slug) }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-cream">
                    <span class="grid size-9 place-items-center rounded-xl bg-sand">📂</span><span>Раздел: <b>{{ $c->name }}</b></span>
                </a>
            @empty
            @endforelse
            @foreach ($products as $p)
                <a href="{{ route('product', $p->slug) }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-cream">
                    <span class="size-10 shrink-0 overflow-hidden rounded-xl bg-sand">
                        @if ($p->imageUrl()) <img src="{{ $p->imageUrl() }}" alt="" class="size-full object-cover"> @endif
                    </span>
                    <span class="min-w-0 flex-1 truncate">{{ $p->name }}</span>
                    <b class="whitespace-nowrap">{{ $p->formattedPrice() }}</b>
                </a>
            @endforeach
            @if ($products->isEmpty() && $categories->isEmpty())
                <p class="px-4 py-4 text-sm text-mocha">Ничего не нашлось по «{{ $term }}»</p>
            @else
                <button class="block w-full border-t border-cocoa/5 px-4 py-3 text-left text-sm font-semibold text-berry hover:bg-cream">Все результаты →</button>
            @endif
        </div>
    @endif
</form>
