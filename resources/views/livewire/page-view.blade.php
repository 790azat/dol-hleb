<div class="container-x py-10">
    <nav class="text-sm text-mocha">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-berry">Главная</a>
        <span class="mx-1.5">/</span><span class="text-cocoa">{{ $page->title }}</span>
    </nav>
    <h1 class="mt-4 text-4xl font-bold sm:text-5xl">{{ $page->title }}</h1>

    <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_340px]">
        <div>
            @if ($page->body)
                <div class="prose-shop max-w-3xl text-[17px]">{!! $page->body !!}</div>
            @elseif ($page->slug !== 'otzyvy')
                <p class="text-mocha">Информация скоро появится. Звоните — расскажем всё по телефону.</p>
            @endif

            @if (! empty($page->images))
                <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-3">
                    @foreach ($page->images as $img)
                        <img src="{{ asset('images/'.$img) }}" alt="" loading="lazy" class="aspect-square w-full rounded-3xl object-cover">
                    @endforeach
                </div>
            @endif

            @if ($page->slug === 'otzyvy')
                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach ($reviews as $review)
                        <figure class="rounded-3xl bg-white p-6 ring-1 ring-cocoa/5">
                            <div class="text-crust">{{ str_repeat('★', $review->rating) }}</div>
                            <blockquote class="mt-3">«{{ $review->text }}»</blockquote>
                            <figcaption class="mt-4 text-sm font-semibold text-mocha">{{ $review->author }} · {{ $review->created_at->translatedFormat('d.m.Y') }}</figcaption>
                        </figure>
                    @endforeach
                </div>
                <form wire:submit="sendReview" class="mt-10 max-w-xl space-y-4 rounded-[2rem] bg-white p-6 ring-1 ring-cocoa/5">
                    <h2 class="text-2xl font-bold">Оставить отзыв</h2>
                    @if ($sent)
                        <p class="rounded-2xl bg-crust/15 p-3 text-sm">Спасибо! Отзыв появится после проверки.</p>
                    @endif
                    <div>
                        <input wire:model="author" class="input" placeholder="Ваше имя">
                        @error('author') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <textarea wire:model="text" rows="4" class="input" placeholder="Расскажите, как вам наши торты и выпечка"></textarea>
                        @error('text') <p class="mt-1 text-sm text-berry">{{ $message }}</p> @enderror
                    </div>
                    <button class="btn-primary">Отправить</button>
                </form>
            @endif
        </div>

        <aside class="h-fit space-y-4 rounded-[2rem] bg-cocoa p-7 text-cream lg:sticky lg:top-28">
            <h3 class="text-2xl font-bold">Есть вопрос?</h3>
            @foreach (config('shop.phones') as $ph)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $ph['number']) }}" class="block"><b class="text-lg">{{ $ph['number'] }}</b><br><span class="text-sm text-cream/60">{{ $ph['label'] }}</span></a>
            @endforeach
            <p class="border-t border-cream/10 pt-4 text-sm text-cream/70">{{ config('shop.address') }}<br>{{ config('shop.hours') }}</p>
        </aside>
    </div>
</div>
