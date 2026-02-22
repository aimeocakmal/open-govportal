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

    @vite([$themeViteEntries['css'], $themeViteEntries['js']])
    @livewireStyles
</head>
<body class="bg-bg-washed text-text font-sans text-body-md antialiased flex items-center justify-center min-h-full">

    {{ $slot }}

    @livewireScripts
</body>
</html>
