<footer class="mt-24 bg-cocoa text-cream/70">
    <div class="container-x grid gap-10 py-14 md:grid-cols-2 lg:grid-cols-4">
        <div class="space-y-4">
            <x-logo light />
            <p class="text-sm leading-relaxed">Свежий хлеб, выпечка и торты на заказ любой сложности. Без ГМО, пищевых добавок и некачественного сырья.</p>
        </div>
        <div>
            <h4 class="mb-4 font-sans text-sm font-bold uppercase tracking-widest text-cream">Каталог</h4>
            <ul class="space-y-2 text-sm">
                @foreach (\App\Models\Category::visible()->roots()->limit(7)->get() as $c)
                    <li><a href="{{ route('catalog', $c->slug) }}" wire:navigate class="hover:text-white">{{ $c->name }}</a></li>
                @endforeach
            </ul>
        </div>
        <div>
            <h4 class="mb-4 font-sans text-sm font-bold uppercase tracking-widest text-cream">Покупателям</h4>
            <ul class="space-y-2 text-sm">
                @foreach (['o-nas' => 'О нас', 'otzyvy' => 'Отзывы', 'dostavka' => 'Доставка', 'oplata' => 'Оплата', 'aktsii' => 'Акции'] as $slug => $t)
                    <li><a href="{{ route('page', $slug) }}" wire:navigate class="hover:text-white">{{ $t }}</a></li>
                @endforeach
                <li><a href="{{ route('contacts') }}" wire:navigate class="hover:text-white">Контакты</a></li>
            </ul>
        </div>
        <div class="space-y-3 text-sm">
            <h4 class="mb-4 font-sans text-sm font-bold uppercase tracking-widest text-cream">Контакты</h4>
            @foreach (config('shop.phones') as $ph)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $ph['number']) }}" class="block"><span class="font-bold text-cream">{{ $ph['number'] }}</span><br><span class="text-xs">{{ $ph['label'] }}</span></a>
            @endforeach
            <p>{{ config('shop.address') }}<br>{{ config('shop.hours') }}</p>
        </div>
    </div>
    <div class="border-t border-cream/10">
        <div class="container-x flex flex-col items-center justify-between gap-2 py-6 text-xs sm:flex-row">
            <span>© {{ date('Y') }} Дол-Хлеб, Долгопрудный</span>
            <span class="flex gap-4">
                <a href="{{ config('shop.social.instagram') }}" target="_blank" rel="noopener" class="hover:text-white">Instagram</a>
                <a href="{{ config('shop.social.facebook') }}" target="_blank" rel="noopener" class="hover:text-white">Facebook</a>
            </span>
        </div>
    </div>
</footer>
