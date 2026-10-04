<div class="container-x grid place-items-center py-20">
    <form wire:submit="login" class="w-full max-w-sm space-y-4 rounded-[2rem] bg-white p-8 ring-1 ring-cocoa/5">
        <h1 class="text-3xl font-bold">Админка</h1>
        <input wire:model="password" type="password" class="input" placeholder="Пароль" autofocus>
        @error('password') <p class="text-sm text-berry">{{ $message }}</p> @enderror
        <button class="btn-dark w-full">Войти</button>
    </form>
</div>
