<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-theme="{{ $currentTheme ?? 'default' }}"
    class="h-full"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name') }}</title>

    {{-- Canonical and hreflang for bilingual SEO --}}
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="ms" href="{{ preg_replace('#/(ms|en)(/)#', '/ms$2', url()->current()) }}">
    <link rel="alternate" hreflang="en" href="{{ preg_replace('#/(ms|en)(/)#', '/en$2', url()->current()) }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-bg text-text font-sans antialiased flex flex-col min-h-full">

    <x-layout.nav />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-layout.footer />

    @livewireScripts
</body>
</html>
