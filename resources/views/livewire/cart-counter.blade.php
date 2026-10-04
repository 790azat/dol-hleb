<a href="{{ route('cart') }}" wire:navigate class="relative flex h-11 items-center gap-2 rounded-full bg-cocoa px-4 text-sm font-semibold text-cream transition hover:bg-black">
    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 016 0"/></svg>
    <span class="hidden sm:inline">Корзина</span>
    @if ($count)
        <span class="grid min-w-6 place-items-center rounded-full bg-crust px-1.5 text-xs font-bold text-cocoa">{{ $count }}</span>
    @endif
</a>
