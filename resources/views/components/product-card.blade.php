@props(['product'])
<article wire:key="p-{{ $product->id }}" class="group flex flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-cocoa/5 transition hover:-translate-y-1 hover:shadow-xl hover:shadow-cocoa/10">
    <a href="{{ route('product', $product->slug) }}" wire:navigate class="relative block aspect-square overflow-hidden bg-sand">
        @if ($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <div class="grid size-full place-items-center text-5xl text-wheat">🥐</div>
        @endif
        @if ($product->is_new)
            <span class="absolute left-3 top-3 rounded-full bg-berry px-3 py-1 text-xs font-bold text-white">Новинка</span>
        @endif
    </a>
    <div class="flex flex-1 flex-col gap-3 p-4">
        <a href="{{ route('product', $product->slug) }}" wire:navigate class="line-clamp-2 text-[15px] font-semibold leading-snug hover:text-berry">{{ $product->name }}</a>
        @if (! empty($product->params[0]))
            <p class="truncate text-xs text-mocha">{{ $product->params[0]['name'] }}: {{ $product->params[0]['value'] }}</p>
        @endif
        <div class="mt-auto flex items-center justify-between gap-2">
            <span class="text-lg font-bold">{{ $product->formattedPrice() }}</span>
            @if ($product->price)
                <button wire:click="addToCart({{ $product->id }})" wire:loading.attr="disabled" wire:target="addToCart({{ $product->id }})"
                        class="grid size-11 place-items-center rounded-full bg-cocoa text-cream transition hover:bg-berry" aria-label="В корзину">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                </button>
            @endif
        </div>
    </div>
</article>
