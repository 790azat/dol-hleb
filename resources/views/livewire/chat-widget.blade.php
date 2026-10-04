<div class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3">
    @if ($open)
        <div wire:poll.4s class="flex h-[min(520px,75vh)] w-[min(360px,calc(100vw-2.5rem))] flex-col overflow-hidden rounded-[1.75rem] bg-white shadow-2xl shadow-cocoa/25 ring-1 ring-cocoa/10">
            <div class="flex items-center gap-3 bg-cocoa px-5 py-4 text-cream">
                <span class="relative grid size-10 place-items-center rounded-full bg-crust text-lg">🧁
                    <span class="absolute bottom-0 right-0 size-3 rounded-full border-2 border-cocoa bg-green-400"></span>
                </span>
                <div class="leading-tight">
                    <b class="block">Дол-Хлеб</b>
                    <span class="text-xs text-cream/60">Ответим в рабочее время, 8:00–20:00</span>
                </div>
                <button wire:click="toggle" class="ml-auto text-cream/60 hover:text-white" aria-label="Закрыть">✕</button>
            </div>
            <div class="flex-1 space-y-2 overflow-y-auto bg-cream p-4" x-data x-init="$el.scrollTop = $el.scrollHeight" x-effect="$el.scrollTop = $el.scrollHeight">
                <div class="max-w-[85%] rounded-2xl rounded-bl-md bg-white px-4 py-2.5 text-sm shadow-sm">Здравствуйте! Подскажем с выбором торта, начинкой и сроками. Напишите вопрос 👇</div>
                @foreach ($messages as $m)
                    <div wire:key="m-{{ $m->id }}" class="flex {{ $m->sender === 'visitor' ? 'justify-end' : '' }}">
                        <div class="max-w-[85%] whitespace-pre-line rounded-2xl px-4 py-2.5 text-sm shadow-sm {{ $m->sender === 'visitor' ? 'rounded-br-md bg-cocoa text-cream' : 'rounded-bl-md bg-white' }}">{{ $m->body }}<span class="mt-1 block text-[10px] opacity-50">{{ $m->created_at->format('H:i') }}</span></div>
                    </div>
                @endforeach
            </div>
            <form wire:submit="send" class="space-y-2 border-t border-cocoa/10 p-3">
                @unless ($hasName)
                    <input wire:model="name" class="input py-2" placeholder="Ваше имя (необязательно)">
                @endunless
                <div class="flex gap-2">
                    <input wire:model="text" class="input py-2" placeholder="Сообщение…" autocomplete="off">
                    <button class="grid size-11 shrink-0 place-items-center rounded-full bg-berry text-white hover:bg-berry-dark" aria-label="Отправить">
                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </div>
            </form>
        </div>
    @endif
    <button wire:click="toggle" class="grid size-14 place-items-center rounded-full bg-berry text-white shadow-xl shadow-berry/30 transition hover:scale-105" aria-label="Чат">
        @if ($open)
            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
        @else
            <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5h16v11H8l-4 4V5z"/></svg>
        @endif
    </button>
</div>
