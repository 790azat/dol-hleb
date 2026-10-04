<div x-data="{ show: false, name: '', t: null }"
     @cart-updated.window="if ($event.detail.name) { name = $event.detail.name; show = true; clearTimeout(t); t = setTimeout(() => show = false, 2600) }"
     class="pointer-events-none fixed inset-x-0 bottom-5 z-50 flex justify-center px-4">
    <div x-show="show" x-cloak x-transition.opacity.scale.90
         class="pointer-events-auto flex items-center gap-3 rounded-full bg-cocoa py-2.5 pl-3 pr-5 text-sm text-cream shadow-2xl">
        <span class="grid size-7 place-items-center rounded-full bg-crust text-cocoa">✓</span>
        <span><b x-text="name"></b> — в корзине</span>
        <a href="{{ route('cart') }}" wire:navigate class="ml-2 font-bold text-wheat underline-offset-4 hover:underline">Оформить</a>
    </div>
</div>
