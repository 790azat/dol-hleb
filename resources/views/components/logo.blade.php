@props(['light' => false])
<a href="{{ route('home') }}" wire:navigate {{ $attributes->merge(['class' => 'flex items-center gap-2.5 shrink-0']) }}>
    <span class="grid size-10 place-items-center rounded-2xl {{ $light ? 'bg-cream text-cocoa' : 'bg-cocoa text-cream' }}">
        <svg viewBox="0 0 64 64" class="size-7"><path d="M10 40c0-12 10-22 22-22s22 10 22 22c0 3-2 6-6 6H16c-4 0-6-3-6-6z" fill="#c7843f"/><path d="M23 27l3 11M32 25v13M41 27l-3 11" stroke="currentColor" stroke-width="3.2" stroke-linecap="round"/></svg>
    </span>
    <span class="leading-none">
        <span class="block font-display text-xl font-bold {{ $light ? 'text-cream' : 'text-cocoa' }}">Дол-Хлеб</span>
        <span class="hidden min-[400px]:block text-[10px] font-semibold uppercase tracking-[.22em] {{ $light ? 'text-wheat' : 'text-mocha' }}">пекарня · кондитерская</span>
    </span>
</a>
