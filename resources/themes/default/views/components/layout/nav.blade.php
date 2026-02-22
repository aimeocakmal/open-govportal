@php
    $locale   = app()->getLocale();
    $labelKey = 'label_' . $locale;
    $navItems = $navItems ?? [];

    // Language switcher: swap the locale segment in the current URL path
    $currentPath = request()->path(); // e.g. "ms/siaran"
    $msUrl = '/' . preg_replace('#^(ms|en)(/|$)#', 'ms$2', $currentPath);
    $enUrl = '/' . preg_replace('#^(ms|en)(/|$)#', 'en$2', $currentPath);
@endphp

<header
    x-data="{ open: false }"
    class="bg-white border-b border-border shadow-sm sticky top-0 z-50"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="/{{ $locale }}" class="flex items-center gap-3 shrink-0">
                <span class="font-bold text-lg text-primary leading-tight">
                    {{ config('app.name') }}
                </span>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-6" aria-label="Main navigation">
                @foreach ($navItems as $item)
                    <a
                        href="/{{ $locale }}/{{ $item['url'] }}"
                        class="text-sm font-medium text-gray-600 hover:text-primary transition-colors
                               {{ request()->is($locale . '/' . $item['url'] . '*') ? 'text-primary font-semibold' : '' }}"
                    >
                        {{ $item[$labelKey] ?? $item['label_ms'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Right side: language switcher + theme switcher --}}
            <div class="hidden md:flex items-center gap-4">
                {{-- Language switcher --}}
                <div class="flex items-center gap-1 text-sm font-medium">
                    <a
                        href="{{ $msUrl }}"
                        class="{{ $locale === 'ms' ? 'text-primary font-semibold' : 'text-gray-500 hover:text-primary' }} transition-colors"
                        hreflang="ms"
                        aria-label="Bahasa Malaysia"
                    >BM</a>
                    <span class="text-gray-300">|</span>
                    <a
                        href="{{ $enUrl }}"
                        class="{{ $locale === 'en' ? 'text-primary font-semibold' : 'text-gray-500 hover:text-primary' }} transition-colors"
                        hreflang="en"
                        aria-label="English"
                    >EN</a>
                </div>

                <x-layout.theme-switcher />
            </div>

            {{-- Mobile hamburger --}}
            <button
                @click="open = !open"
                type="button"
                class="md:hidden p-2 rounded-md text-gray-500 hover:text-primary hover:bg-surface transition-colors"
                :aria-expanded="open"
                aria-controls="mobile-menu"
                aria-label="Toggle navigation"
            >
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div
        id="mobile-menu"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="md:hidden border-t border-border bg-white"
    >
        <nav class="px-4 py-3 flex flex-col gap-1" aria-label="Mobile navigation">
            @foreach ($navItems as $item)
                <a
                    href="/{{ $locale }}/{{ $item['url'] }}"
                    @click="open = false"
                    class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-surface hover:text-primary transition-colors
                           {{ request()->is($locale . '/' . $item['url'] . '*') ? 'bg-surface text-primary font-semibold' : '' }}"
                >
                    {{ $item[$labelKey] ?? $item['label_ms'] }}
                </a>
            @endforeach
        </nav>

        {{-- Mobile language + theme --}}
        <div class="px-4 py-3 border-t border-border flex items-center gap-4">
            <div class="flex items-center gap-1 text-sm font-medium">
                <a href="{{ $msUrl }}" class="{{ $locale === 'ms' ? 'text-primary font-semibold' : 'text-gray-500' }}">BM</a>
                <span class="text-gray-300">|</span>
                <a href="{{ $enUrl }}" class="{{ $locale === 'en' ? 'text-primary font-semibold' : 'text-gray-500' }}">EN</a>
            </div>
            <x-layout.theme-switcher />
        </div>
    </div>
</header>
