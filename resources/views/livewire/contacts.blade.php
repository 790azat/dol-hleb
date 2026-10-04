<div class="container-x py-10">
    <h1 class="text-4xl font-bold sm:text-5xl">Контакты</h1>
    <div class="mt-8 grid gap-6 lg:grid-cols-[400px_1fr]">
        <div class="space-y-4">
            @foreach (config('shop.phones') as $ph)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $ph['number']) }}" class="flex items-center gap-4 rounded-3xl bg-white p-5 ring-1 ring-cocoa/5 transition hover:ring-crust">
                    <span class="grid size-12 place-items-center rounded-2xl bg-sand text-xl">📞</span>
                    <span><b class="block text-lg">{{ $ph['number'] }}</b><span class="text-sm text-mocha">{{ $ph['label'] }}</span></span>
                </a>
            @endforeach
            <div class="flex items-center gap-4 rounded-3xl bg-white p-5 ring-1 ring-cocoa/5">
                <span class="grid size-12 place-items-center rounded-2xl bg-sand text-xl">📍</span>
                <span><b class="block">{{ config('shop.address') }}</b><span class="text-sm text-mocha">{{ config('shop.hours') }}</span></span>
            </div>
            <div class="flex gap-3">
                <a href="{{ config('shop.social.instagram') }}" target="_blank" rel="noopener" class="btn-ghost flex-1">Instagram</a>
                <a href="{{ config('shop.social.facebook') }}" target="_blank" rel="noopener" class="btn-ghost flex-1">Facebook</a>
            </div>
        </div>
        <div class="min-h-[420px] overflow-hidden rounded-[2rem] bg-sand ring-1 ring-cocoa/5">
            <iframe title="Карта" class="size-full min-h-[420px]" loading="lazy"
                    src="https://yandex.ru/map-widget/v1/?text={{ urlencode(config('shop.map_query')) }}&z=16"></iframe>
        </div>
    </div>
</div>
