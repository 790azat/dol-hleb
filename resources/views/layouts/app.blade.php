<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) && $title ? $title.' — '.config('shop.name') : config('shop.name').' — торты на заказ, хлеб и выпечка в Долгопрудном' }}</title>
    <meta name="description" content="{{ $description ?? 'Пекарня и кондитерская «Дол-Хлеб» в Долгопрудном: торты на заказ любой сложности, свежий хлеб, выпечка, пирожные и десерты.' }}">
    <meta name="theme-color" content="#2b1d14">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-cream text-cocoa antialiased flex flex-col">
    <x-header />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-footer />

    <x-cart-toast />
    @unless (request()->routeIs('admin*'))
        @persist('chat')
            <livewire:chat-widget />
        @endpersist
    @endunless
    @livewireScripts
</body>
</html>
