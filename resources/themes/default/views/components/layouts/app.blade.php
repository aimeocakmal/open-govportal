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

    @stack('seo')

    {{-- Accessibility: apply saved settings before first paint to prevent flash --}}
    <script>
    (function(){
        var s=localStorage,d=document.documentElement.dataset;
        d.a11yFontsize=s.getItem('a11y-fontsize')||'default';
        d.a11yFonttype=s.getItem('a11y-fonttype')||'default';
        d.a11yBgcolor=s.getItem('a11y-bgcolor')||'default';
        d.a11yContrast=s.getItem('a11y-contrast')||'default';
    })();
    </script>

    @vite([$themeViteEntries['css'], $themeViteEntries['js']])
    @livewireStyles

    {{-- Google Analytics --}}
    @if($gaId = \App\Models\Setting::get('google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
        <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','{{ $gaId }}');</script>
    @endif

    {{-- Custom analytics (Facebook Pixel, Hotjar, etc.) --}}
    @if($customScript = \App\Models\Setting::get('custom_analytics_script'))
        {!! $customScript !!}
    @endif
</head>
<body class="bg-bg text-text font-sans text-body-md antialiased flex flex-col min-h-full">

    <x-layout.masthead />
    <x-layout.nav />

    <main id="main-content" class="flex-1">
        {{ $slot }}
    </main>

    <x-layout.footer />

    <x-layout.accessibility-menu />

    <livewire:ai-chat />

    @livewireScripts
</body>
</html>
